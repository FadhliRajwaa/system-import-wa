<?php

namespace App\Services;

use App\Models\PesanWa;
use App\Models\Peserta;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PesanWaService
{
    /**
     * Maximum length for error messages stored in database
     */
    private const MAX_ERROR_LENGTH = 1000;

    /**
     * Send WhatsApp message based on provider setting
     */
    public function sendNow(PesanWa $pesan, Peserta $peserta): array
    {
        $provider = config('services.whatsapp.provider', 'manual');

        return match($provider) {
            'wablas' => $this->sendViaWablas($pesan, $peserta),
            'meta_cloud_api' => $this->sendViaMetaCloudApi($pesan, $peserta),
            'twilio' => $this->sendViaTwilio($pesan, $peserta),
            default => $this->sendViaManual($pesan, $peserta), // wa.me links
        };
    }
    
    /**
     * Manual mode: Generate wa.me link for individual sending
     * User will click button to open WhatsApp Web with pre-filled message
     */
    protected function sendViaManual(PesanWa $pesan, Peserta $peserta): array
    {
        try {
            // Generate wa.me URL
            $phone = $this->formatPhoneForWaMe($pesan->no_tujuan);
            $message = urlencode($pesan->isi_pesan);
            $waUrl = "https://wa.me/{$phone}?text={$message}";

            // Update status to 'belum_kirim' (user will manually send via wa.me link)
            // Note: pesan_wa.status ENUM is: 'belum_kirim', 'success', 'gagal'
            $pesan->update([
                'status' => 'belum_kirim',
                'waktu_kirim' => now(),
            ]);

            // Note: peserta.status_wa ENUM is: 'not_sent', 'queued', 'sent', 'failed'
            $peserta->update([
                'status_wa' => 'queued',
                'error_wa' => null
            ]);

            return [
                'success' => true,
                'mode' => 'manual',
                'url' => $waUrl,
                'phone' => $phone
            ];

        } catch (\Exception $e) {
            Log::error('WA Manual Error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Generate wa.me URL for opening in browser
     */
    public function generateWaUrl(string $phone, string $message): string
    {
        $formattedPhone = $this->formatPhoneForWaMe($phone);
        $encodedMessage = urlencode($message);
        return "https://wa.me/{$formattedPhone}?text={$encodedMessage}";
    }
    
    /**
     * Format phone number for wa.me (remove +, spaces, dashes)
     */
    protected function formatPhoneForWaMe(string $phone): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // If starts with 0, replace with 62 (Indonesia)
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }
        
        return $phone;
    }

    /**
     * Meta Cloud API sending (for future blast feature)
     */
    protected function sendViaMetaCloudApi(PesanWa $pesan, Peserta $peserta): array
    {
        try {
            $phoneNumberId = config('services.whatsapp.phone_number_id');
            $accessToken = config('services.whatsapp.access_token');
            
            if (empty($phoneNumberId) || empty($accessToken)) {
                throw new \Exception('WhatsApp API configuration missing (ID or Token)');
            }

            $apiUrl = "https://graph.facebook.com/v19.0/{$phoneNumberId}/messages";
            
            $response = Http::withToken($accessToken)->post($apiUrl, [
                'messaging_product' => 'whatsapp',
                'to' => $pesan->no_tujuan,
                'type' => 'text',
                'text' => [
                    'body' => $pesan->isi_pesan,
                ],
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $providerId = $data['messages'][0]['id'] ?? 'meta_'.uniqid();

                $pesan->update([
                    'status' => 'success',
                    'waktu_kirim' => now(),
                ]);

                $peserta->update([
                    'status_wa' => 'sent',
                    'waktu_kirim_wa' => now(),
                    'error_wa' => null
                ]);

                return ['success' => true, 'mode' => 'api', 'id' => $providerId];
            } else {
                throw new \Exception('Meta Cloud API error: ' . $response->body());
            }

        } catch (\Exception $e) {
            $errorMsg = $this->truncateError($e->getMessage());

            $pesan->update([
                'status' => 'gagal',
                'percobaan' => $pesan->percobaan + 1,
                'error_terakhir' => $errorMsg,
            ]);

            $peserta->update([
                'status_wa' => 'failed',
                'error_wa' => $errorMsg
            ]);

            Log::error('WA API Send Error: ' . $e->getMessage());
            return ['success' => false, 'error' => $errorMsg];
        }
    }

    /**
     * Twilio sending (placeholder for future)
     */
    protected function sendViaTwilio(PesanWa $pesan, Peserta $peserta): array
    {
        // TODO: Implement Twilio integration
        return ['success' => false, 'error' => 'Twilio not implemented yet'];
    }
    
    /**
     * Wablas API sending - uses authenticated user's token
     * Supports sending message with PDF file attachment
     * API Docs: https://wablas.com/documentation/api
     */
    protected function sendViaWablas(PesanWa $pesan, Peserta $peserta): array
    {
        try {
            $user = \Illuminate\Support\Facades\Auth::user();

            if (!$user || empty($user->wablas_token) || empty($user->wablas_secret_key)) {
                throw new \Exception('Wablas token dan secret key belum dikonfigurasi untuk user ini');
            }

            // Format phone number
            $phone = $this->formatPhoneForWaMe($pesan->no_tujuan);

            // Check if peserta has PDF to send document or just text
            if ($peserta->sudahAdaPdf() && $peserta->path_pdf) {
                // Send document with caption using Wablas API
                return $this->sendWablasDocument($user, $phone, $pesan, $peserta);
            } else {
                // Send text message only
                return $this->sendWablasText($user, $phone, $pesan, $peserta);
            }

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // Connection timeout or network error
            $errorMsg = 'Wablas connection timeout - server tidak merespon';
            Log::error('Wablas Connection Error: ' . $e->getMessage());

            $pesan->update([
                'status' => 'gagal',
                'percobaan' => $pesan->percobaan + 1,
                'error_terakhir' => $errorMsg,
            ]);

            $peserta->update([
                'status_wa' => 'failed',
                'error_wa' => $errorMsg
            ]);

            return ['success' => false, 'error' => $errorMsg];

        } catch (\Exception $e) {
            $errorMsg = $this->truncateError($e->getMessage());

            $pesan->update([
                'status' => 'gagal',
                'percobaan' => $pesan->percobaan + 1,
                'error_terakhir' => $errorMsg,
            ]);

            $peserta->update([
                'status_wa' => 'failed',
                'error_wa' => $errorMsg
            ]);

            Log::error('Wablas Send Error: ' . $e->getMessage());
            return ['success' => false, 'error' => $errorMsg];
        }
    }

    /**
     * Send text message via Wablas API
     * POST https://wablas.com/api/send-message
     */
    protected function sendWablasText($user, string $phone, PesanWa $pesan, Peserta $peserta): array
    {
        $apiUrl = config('services.wablas.api_url', 'https://wablas.com') . '/api/send-message';

        $postData = [
            'phone' => $phone,
            'message' => $pesan->isi_pesan,
        ];

        Log::info('Wablas: Sending text message', [
            'phone' => $phone,
            'message_length' => strlen($pesan->isi_pesan),
        ]);

        // Authorization header format: {token}.{secret_key}
        // Docs: https://wablas.com/documentation/api
        $response = Http::timeout(30)
            ->retry(2, 1000)
            ->withHeaders([
                'Authorization' => $user->wablas_token . '.' . $user->wablas_secret_key,
            ])
            ->asForm()
            ->post($apiUrl, $postData);

        return $this->handleWablasResponse($response, $pesan, $peserta, 'text');
    }

    /**
     * Send document (PDF) with caption via Wablas API
     * POST https://wablas.com/api/send-document
     */
    protected function sendWablasDocument($user, string $phone, PesanWa $pesan, Peserta $peserta): array
    {
        $apiUrl = config('services.wablas.api_url', 'https://wablas.com') . '/api/send-document';

        // Get public URL for the PDF
        $pdfUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($peserta->path_pdf);

        $postData = [
            'phone' => $phone,
            'document' => $pdfUrl,
            'caption' => $pesan->isi_pesan,
        ];

        Log::info('Wablas: Sending document with caption', [
            'phone' => $phone,
            'pdf_url' => $pdfUrl,
            'caption_length' => strlen($pesan->isi_pesan),
        ]);

        // Authorization header format: {token}.{secret_key}
        // Docs: https://wablas.com/documentation/api
        $response = Http::timeout(60) // Longer timeout for document upload
            ->retry(2, 2000)
            ->withHeaders([
                'Authorization' => $user->wablas_token . '.' . $user->wablas_secret_key,
            ])
            ->asForm()
            ->post($apiUrl, $postData);

        return $this->handleWablasResponse($response, $pesan, $peserta, 'document');
    }

    /**
     * Handle Wablas API response
     */
    protected function handleWablasResponse($response, PesanWa $pesan, Peserta $peserta, string $type): array
    {
        Log::info('Wablas API Response', [
            'type' => $type,
            'status' => $response->status(),
            'body' => substr($response->body(), 0, 500),
        ]);

        if ($response->successful()) {
            $data = $response->json();

            // Wablas returns status: true on success
            if (isset($data['status']) && $data['status'] === true) {
                $pesan->update([
                    'status' => 'success',
                    'waktu_kirim' => now(),
                ]);

                $peserta->update([
                    'status_wa' => 'sent',
                    'waktu_kirim_wa' => now(),
                    'error_wa' => null
                ]);

                $messageId = $data['data']['messages'][0]['id'] ?? ($data['data']['id'] ?? 'wablas_' . uniqid());

                return [
                    'success' => true,
                    'mode' => 'wablas',
                    'type' => $type,
                    'id' => $messageId,
                    'data' => $data
                ];
            } else {
                // Wablas returned success HTTP but error in response body
                $errorMsg = $data['message'] ?? $data['error'] ?? 'Unknown Wablas error';
                throw new \Exception($errorMsg);
            }
        } else {
            // HTTP error (4xx, 5xx)
            $statusCode = $response->status();
            throw new \Exception("Wablas API error (HTTP {$statusCode}): " . $response->body());
        }
    }

    /**
     * Truncate error message to prevent database overflow
     * Also extracts meaningful error from HTML responses
     */
    protected function truncateError(string $error): string
    {
        // If error contains HTML (like Wablas internal errors), extract the meaningful part
        if (str_contains($error, '<!DOCTYPE html>') || str_contains($error, '<html')) {
            // Try to extract the actual exception message
            if (preg_match('/Exception:\s*([^<]+)/i', $error, $matches)) {
                $error = 'Wablas Internal Error: ' . trim($matches[1]);
            } else {
                $error = 'Wablas Internal Server Error (service unavailable)';
            }
        }

        // Truncate to max length
        if (strlen($error) > self::MAX_ERROR_LENGTH) {
            return substr($error, 0, self::MAX_ERROR_LENGTH - 3) . '...';
        }

        return $error;
    }
}
