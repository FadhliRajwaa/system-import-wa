<?php

namespace App\Livewire\Settings;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class Wablas extends Component
{
    public string $wablas_token = '';
    public string $wablas_phone = '';
    public string $wablas_secret_key = '';

    public function mount(): void
    {
        $user = Auth::user();
        $this->wablas_token = $user->wablas_token ?? '';
        $this->wablas_phone = $user->wablas_phone ?? '';
        $this->wablas_secret_key = $user->wablas_secret_key ?? '';
    }

    public function updateWablasSettings(): void
    {
        $validated = $this->validate([
            'wablas_token' => ['nullable', 'string', 'max:500'],
            'wablas_phone' => ['nullable', 'string', 'max:20'],
            'wablas_secret_key' => ['nullable', 'string', 'max:500'],
        ]);

        $user = Auth::user();
        $user->update($validated);

        $this->dispatch('wablas-updated');
        session()->flash('status', 'wablas-saved');
    }

    public function testConnection(): void
    {
        if (empty($this->wablas_token)) {
            session()->flash('test-error', 'Token Wablas harus diisi');
            return;
        }

        try {
            // Test connection using Wablas device info API
            // Docs: GET https://wablas.com/api/device/info?token={token}
            $apiUrl = config('services.wablas.api_url', 'https://wablas.com') . '/api/device/info';
            
            $response = Http::timeout(15)
                ->get($apiUrl, ['token' => $this->wablas_token]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['status']) && $data['status'] === true) {
                    $deviceInfo = $data['data'] ?? [];
                    $sender = $deviceInfo['sender'] ?? 'Unknown';
                    $quota = $deviceInfo['quota'] ?? 'N/A';
                    $status = $deviceInfo['status'] ?? 'Unknown';
                    $expiredDate = $deviceInfo['expired_date'] ?? 'N/A';
                    
                    session()->flash('test-success', "Koneksi berhasil! Device: {$sender}, Status: {$status}, Quota: {$quota}, Expired: {$expiredDate}");
                    
                    // Auto-fill phone if available
                    if (!empty($deviceInfo['sender']) && empty($this->wablas_phone)) {
                        $this->wablas_phone = $deviceInfo['sender'];
                    }
                } else {
                    $errorMsg = $data['message'] ?? 'Token tidak valid atau device tidak aktif';
                    session()->flash('test-error', $errorMsg);
                }
            } else {
                session()->flash('test-error', 'Gagal terhubung ke Wablas API (HTTP ' . $response->status() . ')');
            }

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Wablas Connection Test Error: ' . $e->getMessage());
            session()->flash('test-error', 'Connection timeout - tidak dapat terhubung ke server Wablas');
        } catch (\Exception $e) {
            Log::error('Wablas Test Error: ' . $e->getMessage());
            session()->flash('test-error', 'Error: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.settings.wablas');
    }
}
