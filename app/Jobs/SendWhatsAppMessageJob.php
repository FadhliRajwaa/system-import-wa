<?php

namespace App\Jobs;

use App\Models\Participant;
use App\Models\WaMessage;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendWhatsAppMessageJob implements ShouldQueue
{
    use Queueable;

    public $tries = 3;

    public $backoff = [10, 30, 60];

    public $timeout = 60;

    public function __construct(
        public WaMessage $message,
        public Participant $participant
    ) {
        $this->onQueue('whatsapp');
    }

    public function handle(): void
    {
        $rateLimitKey = 'whatsapp:rate_limit';
        $rateLimitPerSecond = config('services.whatsapp.rate_limit_per_second', 1);

        $this->waitForRateLimit($rateLimitKey, $rateLimitPerSecond);

        try {
            $providerMessageId = $this->sendToProvider();

            $this->message->update([
                'status' => 'sent',
                'provider_message_id' => $providerMessageId,
                'sent_at' => now(),
                'attempts' => $this->message->attempts + 1,
            ]);

            $this->participant->update([
                'wa_status' => 'sent',
                'wa_sent_at' => now(),
                'wa_last_error' => null,
            ]);

            Log::info('WhatsApp message sent successfully', [
                'message_id' => $this->message->id,
                'participant_id' => $this->participant->id,
                'provider_message_id' => $providerMessageId,
                'phone' => $this->participant->phone_e164,
            ]);

        } catch (Exception $e) {
            $this->message->update([
                'attempts' => $this->message->attempts + 1,
                'error_message' => $e->getMessage(),
            ]);

            $this->participant->update([
                'wa_last_error' => $e->getMessage(),
            ]);

            Log::error('Failed to send WhatsApp message', [
                'message_id' => $this->message->id,
                'participant_id' => $this->participant->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    protected function sendToProvider(): string
    {
        $provider = config('services.whatsapp.provider', 'meta_cloud_api');

        return match ($provider) {
            'wablas' => $this->sendViaWablas(),
            'meta_cloud_api' => $this->sendViaMetaCloudApi(),
            'twilio' => $this->sendViaTwilio(),
            default => $this->sendMock(),
        };
    }

    protected function sendViaWablas(): string
    {
        // Get the user who created this message
        $user = $this->message->creator;

        if (!$user || empty($user->wablas_token) || empty($user->wablas_secret_key)) {
            throw new Exception('Wablas credentials belum dikonfigurasi');
        }

        $apiUrl = config('services.wablas.api_url', 'https://wablas.com') . '/api/send-message';

        // Format phone number (remove + and ensure starts with country code)
        $phone = preg_replace('/[^0-9]/', '', $this->participant->phone_e164);
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        $response = Http::withHeaders([
            'Authorization' => $user->wablas_token . '.' . $user->wablas_secret_key,
        ])->asForm()->post($apiUrl, [
            'phone' => $phone,
            'message' => $this->message->message_body,
        ]);

        if (!$response->successful()) {
            throw new Exception('Wablas API error: ' . $response->body());
        }

        $data = $response->json();

        if (!isset($data['status']) || $data['status'] !== true) {
            throw new Exception($data['message'] ?? 'Wablas sending failed');
        }

        return 'wablas_' . ($data['data']['messages'][0]['id'] ?? uniqid());
    }

    protected function sendViaMetaCloudApi(): string
    {
        $phoneNumberId = config('services.whatsapp.phone_number_id');
        $accessToken = config('services.whatsapp.access_token');
        $apiUrl = "https://graph.facebook.com/v19.0/{$phoneNumberId}/messages";

        $response = Http::withToken($accessToken)->post($apiUrl, [
            'messaging_product' => 'whatsapp',
            'to' => $this->participant->phone_e164,
            'type' => 'text',
            'text' => [
                'body' => $this->message->message_body,
            ],
        ]);

        if (! $response->successful()) {
            throw new Exception('Meta Cloud API error: '.$response->body());
        }

        $data = $response->json();

        return $data['messages'][0]['id'] ?? 'meta_'.uniqid();
    }

    protected function sendViaTwilio(): string
    {
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $from = config('services.twilio.whatsapp_from');

        $response = Http::withBasicAuth($sid, $token)
            ->asForm()
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                'From' => "whatsapp:{$from}",
                'To' => "whatsapp:{$this->participant->phone_e164}",
                'Body' => $this->message->message_body,
            ]);

        if (! $response->successful()) {
            throw new Exception('Twilio API error: '.$response->body());
        }

        $data = $response->json();

        return $data['sid'] ?? 'twilio_'.uniqid();
    }

    protected function sendMock(): string
    {
        Log::info('Mocking WhatsApp message send', [
            'to' => $this->participant->phone_e164,
            'message' => $this->message->message_body,
        ]);

        return 'mock_'.uniqid();
    }

    protected function waitForRateLimit(string $key, int $maxPerSecond): void
    {
        $lockKey = "{$key}:lock";

        $lock = Cache::lock($lockKey, 10);

        try {
            $lock->block(5);

            $lastSent = Cache::get($key);
            $waitTime = 0;

            if ($lastSent !== null) {
                $elapsed = now()->diffInSeconds($lastSent);
                $waitTime = max(0, (1 / $maxPerSecond) - $elapsed);
            }

            if ($waitTime > 0) {
                sleep((int) $waitTime);
            }

            Cache::put($key, now(), 2);

        } finally {
            $lock?->release();
        }
    }

    public function failed(Exception $exception): void
    {
        $this->message->update([
            'status' => 'failed',
            'attempts' => $this->message->attempts + 1,
        ]);

        $this->participant->update([
            'wa_status' => 'failed',
            'wa_last_error' => $exception->getMessage(),
        ]);

        Log::critical('WhatsApp message job failed permanently', [
            'message_id' => $this->message->id,
            'participant_id' => $this->participant->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
