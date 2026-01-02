<?php

namespace App\Services;

use App\Models\Peserta;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PdfImportService
{
    protected string $storagePath = 'peserta-pdfs';

    /**
     * Process multiple PDF files and match them to peserta by no_lab.
     * OPTIMIZED: Uses batch query instead of N+1
     *
     * @param  array|\Illuminate\Http\UploadedFile[]  $files
     * @param  int  $uploaderId  The user who is uploading
     * @return array  Results with matched, unmatched, and errors
     */
    public function processFiles(array $files, int $uploaderId): array
    {
        $results = [
            'matched' => [],
            'unmatched' => [],
            'errors' => [],
            'total' => count($files),
        ];

        // Step 1: Extract all lab numbers upfront
        $fileLabMap = [];
        foreach ($files as $file) {
            $originalName = $file->getClientOriginalName();
            $noLab = $this->extractLabNumber($originalName);
            $fileLabMap[] = [
                'file' => $file,
                'originalName' => $originalName,
                'noLab' => $noLab,
            ];
        }

        // Step 2: Batch query all peserta by no_lab for this user (1 query)
        $allLabNumbers = array_filter(array_column($fileLabMap, 'noLab'));
        $existingPeserta = collect();
        $existingOtherUser = [];
        
        if (!empty($allLabNumbers)) {
            // Get peserta owned by this user
            $existingPeserta = Peserta::whereIn('no_lab', $allLabNumbers)
                ->where('diupload_oleh', $uploaderId)
                ->get()
                ->keyBy('no_lab');
            
            // Check which lab numbers exist for other users (for better error message)
            $ownedLabNumbers = $existingPeserta->keys()->toArray();
            $notOwnedLabNumbers = array_diff($allLabNumbers, $ownedLabNumbers);
            
            if (!empty($notOwnedLabNumbers)) {
                $existingOtherUser = Peserta::whereIn('no_lab', $notOwnedLabNumbers)
                    ->pluck('no_lab')
                    ->flip()
                    ->toArray();
            }
        }

        // Step 3: Process each file using memory lookup
        foreach ($fileLabMap as $item) {
            try {
                $file = $item['file'];
                $originalName = $item['originalName'];
                $noLab = $item['noLab'];

                if (empty($noLab)) {
                    $results['unmatched'][] = [
                        'matched' => false,
                        'filename' => $originalName,
                        'no_lab' => null,
                        'reason' => 'Format nama file tidak valid. Format yang diharapkan: [no_lab].pdf (contoh: 2501205001.pdf)',
                    ];
                    continue;
                }

                $peserta = $existingPeserta->get($noLab);

                if (!$peserta) {
                    $existsForOther = isset($existingOtherUser[$noLab]);
                    $reason = $existsForOther 
                        ? "Peserta dengan no lab {$noLab} ditemukan, tetapi bukan milik Anda"
                        : "Tidak ditemukan peserta dengan no lab: {$noLab}";
                    
                    $results['unmatched'][] = [
                        'matched' => false,
                        'filename' => $originalName,
                        'no_lab' => $noLab,
                        'reason' => $reason,
                    ];
                    continue;
                }

                // Store the file
                $storedPath = $this->storeFile($file, $noLab);

                // Update peserta
                $peserta->update([
                    'path_pdf' => $storedPath,
                    'status_pdf' => 'uploaded',
                ]);

                $results['matched'][] = [
                    'matched' => true,
                    'filename' => $originalName,
                    'no_lab' => $noLab,
                    'nrp_nip' => $peserta->nrp_nip,
                    'nama' => $peserta->nama,
                    'stored_path' => $storedPath,
                ];
            } catch (Exception $e) {
                $results['errors'][] = [
                    'filename' => $item['originalName'],
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Process a single PDF file.
     */
    protected function processSingleFile(UploadedFile $file, int $uploaderId): array
    {
        $originalName = $file->getClientOriginalName();
        $noLab = $this->extractLabNumber($originalName);

        if (empty($noLab)) {
            return [
                'matched' => false,
                'filename' => $originalName,
                'no_lab' => null,
                'reason' => 'Format nama file tidak valid. Format yang diharapkan: [no_lab].pdf (contoh: 2501205001.pdf)',
            ];
        }

        // Find peserta by no_lab AND diupload_oleh (for user isolation)
        $peserta = Peserta::where('no_lab', $noLab)
            ->where('diupload_oleh', $uploaderId)
            ->first();

        if (! $peserta) {
            // Check if no_lab exists for other user
            $existsForOther = Peserta::where('no_lab', $noLab)->exists();
            $reason = $existsForOther 
                ? "Peserta dengan no lab {$noLab} ditemukan, tetapi bukan milik Anda"
                : "Tidak ditemukan peserta dengan no lab: {$noLab}";
                
            return [
                'matched' => false,
                'filename' => $originalName,
                'no_lab' => $noLab,
                'reason' => $reason,
            ];
        }

        // Store the file
        $storedPath = $this->storeFile($file, $noLab);

        // Update peserta
        $peserta->update([
            'path_pdf' => $storedPath,
            'status_pdf' => 'uploaded',
        ]);

        return [
            'matched' => true,
            'filename' => $originalName,
            'no_lab' => $noLab,
            'nrp_nip' => $peserta->nrp_nip,
            'nama' => $peserta->nama,
            'stored_path' => $storedPath,
        ];
    }

    /**
     * Extract lab number from filename.
     * Expected format: [no_lab].pdf (e.g., "2501205001.pdf" -> "2501205001")
     */
    protected function extractLabNumber(string $filename): ?string
    {
        // Remove .pdf extension
        $name = pathinfo($filename, PATHINFO_FILENAME);

        // Trim whitespace
        $name = trim($name);

        // Allow alphanumeric lab numbers
        if (empty($name)) {
            return null;
        }

        return $name;
    }

    /**
     * Store the PDF file to public storage for download access.
     */
    protected function storeFile(UploadedFile $file, string $noLab): string
    {
        $extension = $file->getClientOriginalExtension();
        $filename = $noLab.'_'.time().'.'.$extension;

        return $file->storeAs($this->storagePath, $filename, 'public');
    }

    /**
     * Get the full path to a stored PDF.
     */
    public function getFullPath(string $storedPath): string
    {
        return Storage::disk('public')->path($storedPath);
    }

    /**
     * Check if a PDF file exists.
     */
    public function fileExists(string $storedPath): bool
    {
        return Storage::disk('public')->exists($storedPath);
    }

    /**
     * Delete a PDF file.
     */
    public function deleteFile(string $storedPath): bool
    {
        return Storage::disk('public')->delete($storedPath);
    }

    /**
     * Process files for admin (can match any peserta).
     * OPTIMIZED: Uses batch query instead of N+1
     */
    public function processFilesAsAdmin(array $files): array
    {
        $results = [
            'matched' => [],
            'unmatched' => [],
            'errors' => [],
            'total' => count($files),
        ];

        // Step 1: Extract all lab numbers upfront
        $fileLabMap = [];
        foreach ($files as $file) {
            $originalName = $file->getClientOriginalName();
            $noLab = $this->extractLabNumber($originalName);
            $fileLabMap[] = [
                'file' => $file,
                'originalName' => $originalName,
                'noLab' => $noLab,
            ];
        }

        // Step 2: Batch query all peserta by no_lab (1 query instead of N)
        $allLabNumbers = array_filter(array_column($fileLabMap, 'noLab'));
        $existingPeserta = [];
        if (!empty($allLabNumbers)) {
            $existingPeserta = Peserta::whereIn('no_lab', $allLabNumbers)
                ->get()
                ->keyBy('no_lab');
        }

        // Step 3: Process each file using memory lookup
        foreach ($fileLabMap as $item) {
            try {
                $file = $item['file'];
                $originalName = $item['originalName'];
                $noLab = $item['noLab'];

                if (empty($noLab)) {
                    $results['unmatched'][] = [
                        'matched' => false,
                        'filename' => $originalName,
                        'no_lab' => null,
                        'reason' => 'Format nama file tidak valid. Format yang diharapkan: [no_lab].pdf (contoh: 2501205001.pdf)',
                    ];
                    continue;
                }

                $peserta = $existingPeserta->get($noLab);

                if (!$peserta) {
                    $results['unmatched'][] = [
                        'matched' => false,
                        'filename' => $originalName,
                        'no_lab' => $noLab,
                        'reason' => "Tidak ditemukan peserta dengan no lab: {$noLab}",
                    ];
                    continue;
                }

                // Store the file
                $storedPath = $this->storeFile($file, $noLab);

                // Update peserta
                $peserta->update([
                    'path_pdf' => $storedPath,
                    'status_pdf' => 'uploaded',
                ]);

                $results['matched'][] = [
                    'matched' => true,
                    'filename' => $originalName,
                    'no_lab' => $noLab,
                    'nrp_nip' => $peserta->nrp_nip,
                    'nama' => $peserta->nama,
                    'stored_path' => $storedPath,
                ];
            } catch (Exception $e) {
                $results['errors'][] = [
                    'filename' => $item['originalName'],
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Process a single PDF file as admin (no user restriction).
     * @deprecated Use processFilesAsAdmin() for batch processing
     */
    protected function processSingleFileAsAdmin(UploadedFile $file): array
    {
        $originalName = $file->getClientOriginalName();
        $noLab = $this->extractLabNumber($originalName);

        if (empty($noLab)) {
            return [
                'matched' => false,
                'filename' => $originalName,
                'no_lab' => null,
                'reason' => 'Format nama file tidak valid. Format yang diharapkan: [no_lab].pdf (contoh: 2501205001.pdf)',
            ];
        }

        // Find peserta by no_lab (any user)
        $peserta = Peserta::where('no_lab', $noLab)->first();

        if (! $peserta) {
            return [
                'matched' => false,
                'filename' => $originalName,
                'no_lab' => $noLab,
                'reason' => "Tidak ditemukan peserta dengan no lab: {$noLab}",
            ];
        }

        // Store the file
        $storedPath = $this->storeFile($file, $noLab);

        // Update peserta
        $peserta->update([
            'path_pdf' => $storedPath,
            'status_pdf' => 'uploaded',
        ]);

        return [
            'matched' => true,
            'filename' => $originalName,
            'no_lab' => $noLab,
            'nrp_nip' => $peserta->nrp_nip,
            'nama' => $peserta->nama,
            'stored_path' => $storedPath,
        ];
    }
}
