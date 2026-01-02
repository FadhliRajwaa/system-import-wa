<?php

namespace App\Services;

use App\Jobs\SendWhatsAppMessageJob;
use App\Models\Participant;
use App\Models\User;
use App\Models\WaMessage;

class WhatsAppService
{
    public function composeMessage(Participant $participant, ?string $messageTemplate = null): string
    {
        // Ambil instansi dari peserta, atau fallback ke DEFAULT
        $instansi = $participant->instansi;

        // Jika instansi tidak ditemukan, gunakan DEFAULT
        if (!$instansi) {
            $instansi = \App\Models\Instansi::where('kode', 'DEFAULT')->first();
        }

        // Hitung tahun anggaran dari tanggal periksa
        $tahunAnggaran = $participant->exam_date ? $participant->exam_date->format('Y') : date('Y');

        // Generate data untuk variabel
        $data = [
            '{{waktu}}' => $participant->exam_date ? $participant->exam_date->format('d/m/Y') : '-',
            '{{no_lab}}' => $participant->lab_number ?? '-',
            '{{nama_pasien}}' => $participant->name ?? '-',
            '{{pangkat}}' => $participant->pangkat ?? '-',
            '{{nrp}}' => $participant->nrp ?? '-',
            '{{satuan_kerja}}' => $participant->satuan_kerja ?? '-',
            '{{tahun_anggaran}}' => $tahunAnggaran,
            '{{link}}' => $participant->link_result ?? '-',
        ];

        // Ambil template dari instansi
        $message = $instansi?->template_prolog ?? '';

        // Jika template kosong, gunakan fallback minimal tanpa emoji
        if (empty(trim($message))) {
            $message = "Hasil pemeriksaan kesehatan: {{link}}";
        }

        // Strip HTML tags dan decode entities
        $message = strip_tags($message);
        $message = html_entity_decode($message, ENT_QUOTES, 'UTF-8');

        $message = str_replace(array_keys($data), array_values($data), $message);

        // Template pesan tambahan jika ada
        if ($messageTemplate) {
            $message .= "\n\n{$messageTemplate}";
        }

        return $message;
    }

    public function sendMessage(Participant $participant, User $sender, ?string $message = null): WaMessage
    {
        $messageBody = $message ?? $this->composeMessage($participant);

        $waMessage = WaMessage::create([
            'provider' => config('services.whatsapp.provider', 'meta_cloud_api'),
            'to_phone_e164' => $participant->phone_e164,
            'message_body' => $messageBody,
            'status' => 'queued',
            'participant_id' => $participant->id,
            'created_by' => $sender->id,
            'attempts' => 0,
        ]);

        SendWhatsAppMessageJob::dispatch($waMessage, $participant)->onQueue('whatsapp');

        return $waMessage;
    }

    public function checkOptIn(Participant $participant): bool
    {
        return $participant->wa_opt_in_at !== null;
    }

    public function canSendMessage(Participant $participant): bool
    {
        if (! $this->checkOptIn($participant)) {
            return false;
        }

        if (empty($participant->phone_e164)) {
            return false;
        }

        $alreadySent = $participant->waMessages()
            ->where('status', 'sent')
            ->exists();

        if ($alreadySent) {
            return false;
        }

        return true;
    }
}
