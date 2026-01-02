<?php

namespace App\Livewire\Participants;

use App\Models\Participant;
use App\Models\ParticipantAttachment;
use App\Models\Upload;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class Show extends Component
{
    use WithFileUploads;

    public Participant $participant;

    public $attachmentFile;

    public bool $showUploadModal = false;

    public bool $showWaModal = false;

    public string $waMessagePreview = '';

    public function mount(Participant $participant): void
    {
        $this->participant = $participant->load(['package', 'company', 'participantAttachments.upload', 'waMessages.user']);
    }

    public function uploadAttachment(): void
    {
        $this->validate([
            'attachmentFile' => 'required|file|max:10240',
        ]);

        $storedPath = $this->attachmentFile->store('attachments', 'local');

        $upload = Upload::create([
            'type' => 'attachment',
            'original_name' => $this->attachmentFile->getClientOriginalName(),
            'stored_path' => $storedPath,
            'mime' => $this->attachmentFile->getMimeType(),
            'size' => $this->attachmentFile->getSize(),
            'status' => 'completed',
            'uploaded_by' => Auth::id(),
        ]);

        ParticipantAttachment::create([
            'original_name' => $this->attachmentFile->getClientOriginalName(),
            'stored_path' => $storedPath,
            'mime' => $this->attachmentFile->getMimeType(),
            'size' => $this->attachmentFile->getSize(),
            'participant_id' => $this->participant->id,
            'upload_id' => $upload->id,
        ]);

        $this->participant->update(['has_attachment' => true]);

        $this->showUploadModal = false;
        $this->attachmentFile = null;
        $this->refresh();
        $this->dispatch('show-toast', type: 'success', message: 'Attachment uploaded successfully');
    }

    public function deleteAttachment(int $id): void
    {
        $attachment = ParticipantAttachment::query()
            ->where('participant_id', $this->participant->id)
            ->findOrFail($id);

        if ($attachment->stored_path && Storage::exists($attachment->stored_path)) {
            Storage::delete($attachment->stored_path);
        }

        $attachment->delete();

        $hasAttachments = $this->participant->participantAttachments()->exists();
        $this->participant->update(['has_attachment' => $hasAttachments]);

        $this->refresh();
        $this->dispatch('show-toast', type: 'success', message: 'Attachment deleted successfully');
    }

    public function prepareWaMessage(): void
    {
        $whatsappService = app(WhatsAppService::class);
        $this->waMessagePreview = $whatsappService->composeMessage($this->participant);
        $this->showWaModal = true;
    }

    public function sendWhatsApp(): void
    {
        $whatsappService = app(WhatsAppService::class);

        if (! $whatsappService->canSendMessage($this->participant)) {
            $this->showWaModal = false;
            $this->dispatch('show-toast', type: 'error', message: 'Cannot send WhatsApp message. Please check opt-in status and phone number.');

            return;
        }

        $whatsappService->sendMessage($this->participant, Auth::user(), $this->waMessagePreview);

        $this->showWaModal = false;
        $this->refresh();
        $this->dispatch('show-toast', type: 'success', message: 'WhatsApp message queued for sending');
    }

    public function downloadAttachment(int $id): mixed
    {
        $attachment = ParticipantAttachment::query()
            ->where('participant_id', $this->participant->id)
            ->findOrFail($id);

        if (! $attachment->stored_path || ! Storage::exists($attachment->stored_path)) {
            $this->dispatch('show-toast', type: 'error', message: 'File not found');

            return null;
        }

        return Storage::download($attachment->stored_path, $attachment->original_name);
    }

    public function refresh(): void
    {
        $this->participant->load(['package', 'company', 'participantAttachments.upload', 'waMessages.user']);
    }

    public function getCanSendWhatsAppProperty(): bool
    {
        return $this->participant->wa_opt_in_at !== null
            && ! empty($this->participant->phone_e164)
            && ! $this->participant->waMessages()->where('status', 'sent')->exists();
    }

    public function getWaStatusBadgeClassProperty(): array
    {
        return [
            'not_sent' => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200',
            'queued' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
            'sent' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
            'failed' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
        ];
    }

    public function getWaStatusLabelProperty(): array
    {
        return [
            'not_sent' => 'Belum Terkirim',
            'queued' => 'Antri',
            'sent' => 'Terkirim',
            'failed' => 'Gagal',
        ];
    }

    public function render()
    {
        return view('livewire.participants.show');
    }
}
