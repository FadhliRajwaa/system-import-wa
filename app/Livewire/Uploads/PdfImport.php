<?php

namespace App\Livewire\Uploads;

use App\Services\PdfImportService;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;

class PdfImport extends Component
{
    use WithFileUploads;

    public array $pdfFiles = [];

    public array $importResults = [];

    public bool $showResults = false;

    // Maximum file size in KB (5MB = 5120KB)
    public const MAX_FILE_SIZE_KB = 5120;
    
    // Maximum number of files per upload
    public const MAX_FILES = 50;

    /**
     * Auto-trigger upload when files are selected
     * This hook is called automatically by Livewire when pdfFiles property changes
     */
    public function updatedPdfFiles(): void
    {
        if (count($this->pdfFiles) > 0) {
            // Small delay to ensure all files are fully uploaded to temp storage
            $this->uploadFiles();
        }
    }

    public function uploadFiles(): void
    {
        try {
            $this->validate([
                'pdfFiles' => 'required|array|min:1|max:' . self::MAX_FILES,
                'pdfFiles.*' => 'required|file|mimes:pdf|max:' . self::MAX_FILE_SIZE_KB,
            ], [
                'pdfFiles.max' => 'Maksimal ' . self::MAX_FILES . ' file per upload.',
                'pdfFiles.*.max' => 'Ukuran file maksimal 5MB per file.',
                'pdfFiles.*.mimes' => 'File harus berformat PDF.',
            ]);

            Log::info('Starting PDF import', ['file_count' => count($this->pdfFiles)]);

            $pdfService = new PdfImportService;
            $user = Auth::user();

            if ($user->isAdmin()) {
                Log::info('Processing as admin (no user restriction)');
                $this->importResults = $pdfService->processFilesAsAdmin($this->pdfFiles);
            } else {
                Log::info('Processing as user', ['user_id' => $user->id]);
                $this->importResults = $pdfService->processFiles($this->pdfFiles, $user->id);
            }

            $matchedCount = count($this->importResults['matched'] ?? []);
            $unmatchedCount = count($this->importResults['unmatched'] ?? []);
            $errorCount = count($this->importResults['errors'] ?? []);

            Log::info('PDF import completed', [
                'matched' => $matchedCount,
                'unmatched' => $unmatchedCount,
                'errors' => $errorCount,
            ]);

            if ($matchedCount > 0 && $unmatchedCount === 0 && $errorCount === 0) {
                $this->dispatch('show-toast', type: 'success', message: "Semua {$matchedCount} file PDF berhasil dicocokkan!");
            } elseif ($matchedCount > 0) {
                $this->dispatch('show-toast', type: 'warning', message: "{$matchedCount} file cocok, {$unmatchedCount} tidak cocok, {$errorCount} error");
            } else {
                $this->dispatch('show-toast', type: 'error', message: 'Tidak ada file yang cocok dengan Nomor Lab peserta');
            }

            $this->showResults = true;
            $this->pdfFiles = [];
            $this->dispatch('pdf-processing-complete');

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('PDF upload validation failed', ['errors' => $e->errors()]);
            $this->dispatch('show-toast', type: 'error', message: 'File tidak valid: ' . implode(', ', $e->validator->errors()->all()));
            $this->dispatch('pdf-processing-complete');
            throw $e;
        } catch (Exception $e) {
            Log::error('PDF upload failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            $this->dispatch('show-toast', type: 'error', message: 'Gagal upload PDF: ' . $e->getMessage());
            $this->dispatch('pdf-processing-complete');
        }
    }

    public function resetUpload(): void
    {
        $this->reset('pdfFiles', 'importResults', 'showResults');
        $this->dispatch('show-toast', type: 'info', message: 'Form direset');
    }

    public function getMatchedCountProperty(): int
    {
        return count($this->importResults['matched'] ?? []);
    }

    public function getUnmatchedCountProperty(): int
    {
        return count($this->importResults['unmatched'] ?? []);
    }

    public function getErrorCountProperty(): int
    {
        return count($this->importResults['errors'] ?? []);
    }

    public function render()
    {
        return view('livewire.uploads.pdf-import');
    }
}
