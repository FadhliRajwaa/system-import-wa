<?php

namespace App\Livewire\Settings;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Livewire\Component;

class Saungwa extends Component
{
    public string $saungwa_appkey = '';
    public string $saungwa_authkey = '';
    public string $saungwa_phone = '';

    public function mount(): void
    {
        $user = Auth::user();
        $this->saungwa_appkey = $user->saungwa_appkey ?? '';
        $this->saungwa_authkey = $user->saungwa_authkey ?? '';
        $this->saungwa_phone = $user->saungwa_phone ?? '';
    }

    public function updateSaungwaSettings(): void
    {
        $validated = $this->validate([
            'saungwa_appkey' => ['nullable', 'string', 'max:500'],
            'saungwa_authkey' => ['nullable', 'string', 'max:500'],
            'saungwa_phone' => ['nullable', 'string', 'max:20'],
        ]);

        $user = Auth::user();
        $user->update($validated);

        $this->dispatch('saungwa-updated');
        session()->flash('status', 'saungwa-saved');
    }

    public function testConnection(): void
    {
        if (empty($this->saungwa_appkey) || empty($this->saungwa_authkey)) {
            session()->flash('test-error', 'App Key dan Auth Key harus diisi');
            return;
        }

        try {
            // Test connection by sending to a test endpoint or checking credentials
            // SaungWA doesn't have a specific test endpoint, so we'll validate by checking the format
            if (strlen($this->saungwa_appkey) < 10 || strlen($this->saungwa_authkey) < 10) {
                session()->flash('test-error', 'Format credentials tidak valid');
                return;
            }

            session()->flash('test-success', 'Credentials tersimpan! Silakan test kirim pesan untuk memastikan koneksi.');

        } catch (\Exception $e) {
            session()->flash('test-error', 'Error: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.settings.saungwa');
    }
}
