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
            'saungwa' => $this->sendViaSaungwa($pesan, $peserta),
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
            
            // Update status to 'pending' (user will manually send)
            $pesan->update([
                'status' => 'pending',
                'waktu_kirim' => now(),
            ]);
            
            $peserta->update([
                'status_wa' => 'pending',
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
     * SaungWA API sending - uses authenticated user's appkey and authkey
     */
    protected function sendViaSaungwa(PesanWa $pesan, Peserta $peserta): array
    {
        try {
            $user = \Illuminate\Support\Facades\Auth::user();

            if (!$user || empty($user->saungwa_appkey) || empty($user->saungwa_authkey)) {
                throw new \Exception('SaungWA credentials belum dikonfigurasi untuk user ini');
            }

            $apiUrl = config('services.saungwa.api_url', 'https://app.saungwa.com/api/create-message');

            // Format phone number
            $phone = $this->formatPhoneForWaMe($pesan->no_tujuan);

            $response = Http::asForm()->post($apiUrl, [
                'appkey' => $user->saungwa_appkey,
                'authkey' => $user->saungwa_authkey,
                'to' => $phone,
                'message' => $pesan->isi_pesan,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                // SaungWA returns message_status in response
                if (isset($data['message_status']) && $data['message_status'] === 'Success') {
                    $pesan->update([
                        'status' => 'success',
                        'waktu_kirim' => now(),
                    ]);

                    $peserta->update([
                        'status_wa' => 'sent',
                        'waktu_kirim_wa' => now(),
                        'error_wa' => null
                    ]);

                    return ['success' => true, 'mode' => 'saungwa', 'data' => $data];
                } else {
                    $errorMsg = $data['message'] ?? 'Unknown SaungWA error';
                    throw new \Exception($errorMsg);
                }
            } else {
                throw new \Exception('SaungWA API error: ' . $response->body());
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

            Log::error('SaungWA Send Error: ' . $e->getMessage());
            return ['success' => false, 'error' => $errorMsg];
        }
    }

    /**
     * Truncate error message to prevent database overflow
     * Also extracts meaningful error from HTML responses
     */
    protected function truncateError(string $error): string
    {
        // If error contains HTML (like SaungWA internal errors), extract the meaningful part
        if (str_contains($error, '<!DOCTYPE html>') || str_contains($error, '<html')) {
            // Try to extract the actual exception message
            if (preg_match('/Exception:\s*([^<]+)/i', $error, $matches)) {
                $error = 'SaungWA Internal Error: ' . trim($matches[1]);
            } else {
                $error = 'SaungWA Internal Server Error (service unavailable)';
            }
        }

        // Truncate to max length
        if (strlen($error) > self::MAX_ERROR_LENGTH) {
            return substr($error, 0, self::MAX_ERROR_LENGTH - 3) . '...';
        }

        return $error;
    }
}
