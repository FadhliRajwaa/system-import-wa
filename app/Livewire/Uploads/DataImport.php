<?php

namespace App\Livewire\Uploads;

use App\Exports\TemplateExport;
use App\Models\Peserta;
use App\Models\Unggahan;
use App\Services\ExcelImportService;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Pagination\LengthAwarePaginator;
use Maatwebsite\Excel\Facades\Excel;

class DataImport extends Component
{
    use WithFileUploads;
    use WithPagination;

    public $uploadFile;

    public array $parsedData = [];

    public bool $previewMode = false;
    
    // Tab yang aktif: 'valid', 'warning', 'invalid'
    public string $activeTab = 'valid';

    public string $duplicationStrategy = 'skip';

    public ?Unggahan $unggahan = null;

    // Property untuk menyimpan error import yang akan ditampilkan di panel
    public array $importErrors = [];
    
    // Property untuk menyimpan warnings (duplikat, konflik no_lab) sebelum import
    public array $validationWarnings = [];
    
    // Property untuk pagination preview data
    public string $previewPerPage = '25';
    
    // ========== 3 KATEGORI DATA PREVIEW ==========
    // 1. Data valid murni (bisa langsung import)
    public array $categorizedValid = [];
    
    // 2. Data valid tapi ada warning (NRP sama dengan tanggal beda, no_lab beda)
    public array $categorizedWarning = [];
    
    // 3. Data tidak valid/error (tidak bisa import)
    public array $categorizedInvalid = [];

    /**
     * Hook yang dipanggil otomatis saat uploadFile berubah (file dipilih)
     */
    public function updatedUploadFile(): void
    {
        $this->processFile();
    }

    public function downloadTemplate()
    {
        try {
            return Excel::download(new TemplateExport, 'template_import_peserta.xlsx');
        } catch (Exception $e) {
            Log::error('Download template failed: ' . $e->getMessage());
            $this->dispatch('show-toast', type: 'error', message: 'Gagal download template: ' . $e->getMessage());
        }
    }

    public function processFile(): void
    {
        try {
            $this->validate([
                'uploadFile' => 'required|file|mimes:xlsx,xls|max:10240',
            ]);

            Log::info('Starting Excel import', ['file' => $this->uploadFile->getClientOriginalName()]);

            $fileName = time().'_'.$this->uploadFile->getClientOriginalName();
            $filePath = $this->uploadFile->storeAs('uploads', $fileName, 'local');
            $fullPath = Storage::disk('local')->path($filePath);

            $importService = new ExcelImportService;
            $this->parsedData = $importService->parse($fullPath);
            
            // Clear previous warnings
            $this->validationWarnings = [];
            $this->importErrors = [];
            
            // Validate data against database (check duplicates and no_lab conflicts)
            $this->validateDataBeforeImport();

            $this->unggahan = Unggahan::create([
                'tipe' => 'data_excel',
                'nama_file_asli' => $this->uploadFile->getClientOriginalName(),
                'path_tersimpan' => $filePath,
                'mime' => $this->uploadFile->getMimeType(),
                'ukuran' => $this->uploadFile->getSize(),
                'status' => 'parsed',
                'total_baris' => count($this->parsedData['valid']) + count($this->parsedData['invalid']),
                'baris_sukses' => 0,
                'baris_gagal' => 0,
                'ringkasan_error' => $this->parsedData['errors'] ?? [],
                'diupload_oleh' => Auth::id(),
            ]);

            $validCount = count($this->categorizedValid);
            $warningCount = count($this->categorizedWarning);
            $invalidCount = count($this->categorizedInvalid);

            Log::info('Excel parsed successfully', [
                'valid_rows' => $validCount,
                'warning_rows' => $warningCount,
                'invalid_rows' => $invalidCount,
            ]);

            if ($validCount > 0 || $warningCount > 0) {
                $message = "File berhasil diparse: {$validCount} valid";
                if ($warningCount > 0) {
                    $message .= ", {$warningCount} perlu perhatian";
                }
                if ($invalidCount > 0) {
                    $message .= ", {$invalidCount} error";
                }
                $this->dispatch('show-toast', type: 'success', message: $message);
            } else {
                $this->dispatch('show-toast', type: 'warning', message: 'Tidak ada baris yang valid dalam file');
            }

            $this->previewMode = true;
            
            // Dispatch event untuk hide loading state di Alpine
            $this->dispatch('processing-complete');

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Upload validation failed', ['errors' => $e->errors()]);
            $this->dispatch('show-toast', type: 'error', message: 'File tidak valid: ' . implode(', ', $e->validator->errors()->all()));
            $this->dispatch('processing-complete');
            throw $e;
        } catch (Exception $e) {
            Log::error('Upload failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            $this->dispatch('show-toast', type: 'error', message: 'Gagal upload file: ' . $e->getMessage());
            $this->dispatch('processing-complete');
        }
    }
    
    /**
     * Validate parsed data against database before import
     * 
     * ATURAN VALIDASI (5 Rules):
     * 1. nrp_nip SAMA + tanggal_periksa SAMA → BLOKIR (Error)
     * 2. nrp_nip SAMA + no_lab SAMA + tanggal_periksa SAMA → BLOKIR (Error)
     * 3. nrp_nip SAMA + tanggal_periksa BEDA + no_lab SAMA → BLOKIR (Error)
     * 4. nrp_nip SAMA + tanggal_periksa BEDA + no_lab BEDA → WARNING (bisa import dengan peringatan)
     * 5. nrp_nip BEDA + tanggal_periksa BEDA + no_lab BEDA → VALID (langsung import)
     * 
     * OUTPUT: 3 Kategori
     * - categorizedValid: Data valid murni
     * - categorizedWarning: Data valid tapi ada NRP sama (pemeriksaan sebelumnya)
     * - categorizedInvalid: Data tidak valid/error
     */
    protected function validateDataBeforeImport(): void
    {
        // Reset kategorisasi
        $this->categorizedValid = [];
        $this->categorizedWarning = [];
        $this->categorizedInvalid = [];
        $this->validationWarnings = [];
        
        // Track data dalam file untuk cek duplikat internal
        $noLabsInFile = [];
        $nrpDateInFile = [];
        
        // ========== BATCH LOAD: Ambil semua data dari DB sekaligus ==========
        // Ini menghilangkan N+1 query problem (dari 2000+ queries menjadi 3 queries)
        
        $allNrps = [];
        $allNoLabs = [];
        $allNrpDates = [];
        
        foreach ($this->parsedData['valid'] as $row) {
            $data = $row['data'];
            if (!empty($data['nrp_nip'])) {
                $allNrps[] = $data['nrp_nip'];
            }
            if (!empty($data['no_lab'])) {
                $allNoLabs[] = $data['no_lab'];
            }
        }
        
        $allNrps = array_unique($allNrps);
        $allNoLabs = array_unique($allNoLabs);
        
        // Query 1: Ambil semua peserta dengan NRP yang ada di file (dengan uploader)
        $existingByNrp = Peserta::whereIn('nrp_nip', $allNrps)
            ->with('uploader')
            ->get()
            ->groupBy('nrp_nip');
        
        // Query 2: Ambil semua peserta dengan No Lab yang ada di file (dengan uploader)
        $existingByNoLab = Peserta::whereIn('no_lab', $allNoLabs)
            ->with('uploader')
            ->get()
            ->keyBy('no_lab');
        
        $currentUserId = Auth::id();
        $currentUserIsAdmin = Auth::user()->isAdmin();
        
        // ========== PROSES SETIAP ROW (tanpa query tambahan) ==========
        
        foreach ($this->parsedData['valid'] as $index => $row) {
            $data = $row['data'];
            $rowNumber = $row['row_number'];
            $nrpNip = $data['nrp_nip'] ?? null;
            $noLab = $data['no_lab'] ?? null;
            $tanggalPeriksa = $data['tanggal_periksa'] ?? null;
            
            // Format tanggal untuk perbandingan
            $tanggalFormatted = null;
            $tanggalDisplay = null;
            if ($tanggalPeriksa instanceof \Carbon\Carbon) {
                $tanggalFormatted = $tanggalPeriksa->format('Y-m-d');
                $tanggalDisplay = $tanggalPeriksa->format('d/m/Y');
            } elseif (is_string($tanggalPeriksa)) {
                $tanggalFormatted = $tanggalPeriksa;
                try {
                    $tanggalDisplay = \Carbon\Carbon::parse($tanggalPeriksa)->format('d/m/Y');
                } catch (\Exception $e) {
                    $tanggalDisplay = $tanggalPeriksa;
                }
            }
            
            $nrpDateKey = $nrpNip && $tanggalFormatted ? "{$nrpNip}_{$tanggalFormatted}" : null;
            $errors = [];
            $warnings = [];
            $category = 'valid'; // default
            
            // ========== CEK DUPLIKAT DALAM FILE ==========
            
            // Cek duplikat NRP+Tanggal dalam file
            if ($nrpDateKey && isset($nrpDateInFile[$nrpDateKey])) {
                $errors[] = [
                    'type' => 'duplicate_nrp_date_file',
                    'message' => "NRP/NIP '{$nrpNip}' dengan tanggal '{$tanggalDisplay}' duplikat dalam file Excel (sama dengan baris {$nrpDateInFile[$nrpDateKey]})",
                    'suggestion' => "Hapus salah satu baris duplikat dari file Excel.",
                ];
                $category = 'invalid';
            } else if ($nrpDateKey) {
                $nrpDateInFile[$nrpDateKey] = $rowNumber;
            }
            
            // Cek duplikat No Lab dalam file
            if ($noLab && isset($noLabsInFile[$noLab])) {
                $errors[] = [
                    'type' => 'duplicate_lab_file',
                    'message' => "No Lab '{$noLab}' duplikat dalam file Excel (sama dengan baris {$noLabsInFile[$noLab]})",
                    'suggestion' => "Setiap peserta harus memiliki No Lab yang unik.",
                ];
                $category = 'invalid';
            } else if ($noLab) {
                $noLabsInFile[$noLab] = $rowNumber;
            }
            
            // ========== CEK TERHADAP DATABASE (menggunakan data yang sudah di-load) ==========
            
            if ($nrpNip && $tanggalFormatted && $category !== 'invalid') {
                // Ambil semua data peserta dengan NRP ini dari cache
                $nrpRecords = $existingByNrp->get($nrpNip, collect());
                
                // Cek apakah ada record dengan tanggal yang sama
                $existingSameDate = $nrpRecords->first(function ($record) use ($tanggalFormatted) {
                    return $record->tanggal_periksa->format('Y-m-d') === $tanggalFormatted;
                });
                
                if ($existingSameDate) {
                    // ATURAN 1 & 2: nrp_nip SAMA + tanggal_periksa SAMA → BLOKIR
                    $ownerInfo = $this->getOwnerInfo($existingSameDate);
                    $isOwner = ($existingSameDate->diupload_oleh === $currentUserId);
                    
                    if (!$isOwner && !$currentUserIsAdmin) {
                        // Data milik user lain - BLOKIR total
                        $errors[] = [
                            'type' => 'ownership_conflict',
                            'message' => "DIBLOKIR: Data sudah diinput oleh {$ownerInfo} - {$existingSameDate->nama} (NRP: {$nrpNip}, Tgl: {$tanggalDisplay})",
                            'suggestion' => "Anda tidak dapat mengimport data yang diinput oleh pengguna lain. Hubungi {$ownerInfo}.",
                            'owner' => $ownerInfo,
                        ];
                        $category = 'invalid';
                    } else {
                        // Data milik sendiri atau admin - bisa update dengan warning
                        $errors[] = [
                            'type' => 'duplicate_same_date',
                            'message' => "Data sudah ada: {$existingSameDate->nama} (NRP: {$nrpNip}, Tgl: {$tanggalDisplay}) - Diinput oleh: {$ownerInfo}",
                            'suggestion' => "Pilih 'Update' untuk mengganti data lama, atau 'Skip' untuk melewati.",
                            'owner' => $ownerInfo,
                        ];
                        $category = 'invalid'; // Sama tanggal = tidak bisa import baru, hanya update
                    }
                } else {
                    // NRP ada tapi tanggal BEDA - cek apakah ada data sebelumnya
                    $existingDifferentDate = $nrpRecords->sortByDesc('tanggal_periksa')->first();
                    
                    if ($existingDifferentDate) {
                        // ATURAN 3: Cek no_lab - jika SAMA dengan data lain → BLOKIR
                        if ($noLab) {
                            $labConflict = $existingByNoLab->get($noLab);
                            
                            // Pastikan conflict bukan record yang sama (NRP+tanggal sama)
                            if ($labConflict && 
                                ($labConflict->nrp_nip !== $nrpNip || 
                                 $labConflict->tanggal_periksa->format('Y-m-d') !== $tanggalFormatted)) {
                                $labOwnerInfo = $this->getOwnerInfo($labConflict);
                                $errors[] = [
                                    'type' => 'duplicate_lab_db',
                                    'message' => "No Lab '{$noLab}' sudah digunakan: {$labConflict->nama} (NRP: {$labConflict->nrp_nip}) - Diinput oleh: {$labOwnerInfo}",
                                    'suggestion' => "No Lab harus unik. Ubah No Lab di file Excel.",
                                    'owner' => $labOwnerInfo,
                                ];
                                $category = 'invalid';
                            }
                        }
                        
                        // ATURAN 4: nrp_nip SAMA + tanggal BEDA + no_lab BEDA → WARNING
                        if ($category !== 'invalid') {
                            $ownerInfo = $this->getOwnerInfo($existingDifferentDate);
                            $existingTglDisplay = $existingDifferentDate->tanggal_periksa->format('d/m/Y');
                            $warnings[] = [
                                'type' => 'existing_nrp_different_date',
                                'message' => "NRP/NIP '{$nrpNip}' sudah ada data pemeriksaan sebelumnya: {$existingDifferentDate->nama} (Tgl: {$existingTglDisplay}) - Diinput oleh: {$ownerInfo}",
                                'suggestion' => "Data ini akan diimport sebagai PEMERIKSAAN BARU. Pilih 'Skip' jika tidak ingin menambah data baru.",
                                'owner' => $ownerInfo,
                                'existing_date' => $existingTglDisplay,
                            ];
                            $category = 'warning';
                        }
                    } else {
                        // ATURAN 5: NRP baru - cek no_lab saja
                        if ($noLab) {
                            $labConflict = $existingByNoLab->get($noLab);
                            if ($labConflict) {
                                $labOwnerInfo = $this->getOwnerInfo($labConflict);
                                $errors[] = [
                                    'type' => 'duplicate_lab_db',
                                    'message' => "No Lab '{$noLab}' sudah digunakan: {$labConflict->nama} (NRP: {$labConflict->nrp_nip}) - Diinput oleh: {$labOwnerInfo}",
                                    'suggestion' => "No Lab harus unik. Ubah No Lab di file Excel.",
                                    'owner' => $labOwnerInfo,
                                ];
                                $category = 'invalid';
                            }
                        }
                    }
                }
            }
            
            // ========== KATEGORISASI DATA ==========
            
            $rowData = [
                'row_number' => $rowNumber,
                'data' => $data,
                'errors' => $errors,
                'warnings' => $warnings,
            ];
            
            if ($category === 'invalid') {
                $this->categorizedInvalid[] = $rowData;
                // Tambahkan ke validationWarnings untuk backward compatibility
                foreach ($errors as $err) {
                    $this->validationWarnings[] = [
                        'type' => $err['type'],
                        'row' => $rowNumber,
                        'field' => $err['type'] === 'duplicate_lab_db' || $err['type'] === 'duplicate_lab_file' ? 'No Lab' : 'NRP/NIP',
                        'message' => $err['message'],
                        'suggestion' => $err['suggestion'],
                        'severity' => 'error',
                        'owner' => $err['owner'] ?? null,
                    ];
                }
            } elseif ($category === 'warning') {
                $this->categorizedWarning[] = $rowData;
                // Tambahkan ke validationWarnings
                foreach ($warnings as $warn) {
                    $this->validationWarnings[] = [
                        'type' => $warn['type'],
                        'row' => $rowNumber,
                        'field' => 'NRP/NIP',
                        'message' => $warn['message'],
                        'suggestion' => $warn['suggestion'],
                        'severity' => 'warning',
                        'owner' => $warn['owner'] ?? null,
                        'existing_date' => $warn['existing_date'] ?? null,
                    ];
                }
            } else {
                $this->categorizedValid[] = $rowData;
            }
        }
        
        // Tambahkan data parse error ke categorizedInvalid
        foreach ($this->parsedData['invalid'] as $row) {
            $this->categorizedInvalid[] = [
                'row_number' => $row['row_number'],
                'data' => $row['data'],
                'errors' => [['type' => 'parse_error', 'message' => $row['error'], 'suggestion' => 'Perbaiki format data di Excel.']],
                'warnings' => [],
                'is_parse_error' => true,
            ];
        }
        
        Log::info('Data categorization completed', [
            'valid' => count($this->categorizedValid),
            'warning' => count($this->categorizedWarning),
            'invalid' => count($this->categorizedInvalid),
        ]);
    }
    
    /**
     * Helper untuk mendapatkan info pemilik data
     */
    protected function getOwnerInfo($peserta): string
    {
        if (!$peserta->diupload_oleh) {
            return 'Admin/System';
        }
        
        if ($peserta->uploader) {
            if ($peserta->uploader->isAdmin()) {
                return 'Admin (' . $peserta->uploader->name . ')';
            }
            return 'User ' . $peserta->diupload_oleh . ' (' . $peserta->uploader->name . ')';
        }
        
        return 'User ' . $peserta->diupload_oleh;
    }

    public function commitImport(): void
    {
        try {
            $this->validate([
                'duplicationStrategy' => 'required|in:skip,update',
            ]);

            // Gunakan importableRows (valid + warning)
            $importableRows = $this->importableRows;
            $successCount = 0;
            $failedCount = 0;
            $skippedCount = 0;
            $errors = [];

            Log::info('Starting commit import', [
                'total_importable' => count($importableRows), 
                'valid' => count($this->categorizedValid),
                'warning' => count($this->categorizedWarning),
                'strategy' => $this->duplicationStrategy
            ]);

            // ========== BATCH LOAD: Ambil semua data existing sekaligus ==========
            $allNrps = [];
            $allNoLabs = [];
            
            foreach ($importableRows as $row) {
                $data = $row['data'];
                if (!empty($data['nrp_nip'])) {
                    $allNrps[] = $data['nrp_nip'];
                }
                if (!empty($data['no_lab'])) {
                    $allNoLabs[] = $data['no_lab'];
                }
            }
            
            $allNrps = array_unique($allNrps);
            $allNoLabs = array_unique($allNoLabs);
            
            // Query 1: Ambil semua peserta dengan NRP yang ada (dengan uploader)
            $existingByNrp = Peserta::whereIn('nrp_nip', $allNrps)
                ->with('uploader')
                ->get()
                ->groupBy('nrp_nip');
            
            // Query 2: Ambil semua peserta dengan No Lab yang ada
            $existingByNoLab = Peserta::whereIn('no_lab', $allNoLabs)
                ->get()
                ->keyBy('no_lab');
            
            $currentUserId = Auth::id();
            $currentUserIsAdmin = Auth::user()->isAdmin();
            
            // ========== BATCH INSERT: Kumpulkan data untuk insert ==========
            $toInsert = [];
            $toUpdate = [];
            $usedNoLabs = []; // Track no_lab yang sudah dipakai dalam batch ini

            foreach ($importableRows as $row) {
                $data = $row['data'];
                
                // Format tanggal_periksa sebagai date only (Y-m-d) untuk perbandingan
                $tanggalPeriksaFormatted = $data['tanggal_periksa'] instanceof \Carbon\Carbon 
                    ? $data['tanggal_periksa']->format('Y-m-d') 
                    : (is_string($data['tanggal_periksa']) ? $data['tanggal_periksa'] : null);

                // NRP/NIP untuk cek duplikasi
                $nrpNip = $data['nrp_nip'] ?? null;
                $noLab = $data['no_lab'] ?? null;

                // Check dari cache - cari record dengan NRP+tanggal yang sama
                $nrpRecords = $existingByNrp->get($nrpNip, collect());
                $existing = $nrpRecords->first(function ($record) use ($tanggalPeriksaFormatted) {
                    return $record->tanggal_periksa->format('Y-m-d') === $tanggalPeriksaFormatted;
                });

                if ($existing) {
                    // OWNERSHIP CHECK
                    $dataOwnerId = $existing->diupload_oleh;
                    $isOwner = ($dataOwnerId === $currentUserId);
                    
                    if (!$isOwner && !$currentUserIsAdmin) {
                        $failedCount++;
                        $ownerInfo = $existing->uploader?->name ?? 'User ' . $dataOwnerId;
                        $this->importErrors[] = [
                            'type' => 'ownership_blocked',
                            'row' => $row['row_number'],
                            'code' => 'ACCESS_DENIED',
                            'message' => "Data NRP '{$nrpNip}' sudah diinput oleh {$ownerInfo}. Anda tidak dapat mengubah data pengguna lain.",
                            'suggestion' => "Hubungi {$ownerInfo} atau admin untuk mengubah data ini.",
                        ];
                        continue;
                    }
                    
                    if ($this->duplicationStrategy === 'skip') {
                        $skippedCount++;
                        continue;
                    }

                    if ($this->duplicationStrategy === 'update') {
                        // Check no_lab conflict dari cache
                        if ($noLab) {
                            $labConflict = $existingByNoLab->get($noLab);
                            if ($labConflict && 
                                ($labConflict->nrp_nip !== $nrpNip || 
                                 $labConflict->tanggal_periksa->format('Y-m-d') !== $tanggalPeriksaFormatted)) {
                                $failedCount++;
                                $this->importErrors[] = [
                                    'type' => 'duplicate_lab',
                                    'row' => $row['row_number'],
                                    'message' => "No Lab '{$noLab}' sudah digunakan peserta lain: {$labConflict->nama} (NRP: {$labConflict->nrp_nip})",
                                    'suggestion' => "Pastikan No Lab unik untuk setiap peserta.",
                                ];
                                continue;
                            }
                        }
                        
                        // Kumpulkan untuk batch update
                        $toUpdate[] = [
                            'existing' => $existing,
                            'data' => $this->prepareParticipantData($data),
                        ];
                    }
                } else {
                    // Validate no_lab uniqueness
                    if ($noLab) {
                        // Cek dari DB cache
                        $labExists = $existingByNoLab->get($noLab);
                        if ($labExists) {
                            $failedCount++;
                            $this->importErrors[] = [
                                'type' => 'duplicate_lab',
                                'row' => $row['row_number'],
                                'message' => "No Lab '{$noLab}' sudah digunakan peserta lain: {$labExists->nama} (NRP: {$labExists->nrp_nip})",
                                'suggestion' => "Pastikan No Lab unik untuk setiap peserta.",
                            ];
                            continue;
                        }
                        
                        // Cek duplikat dalam batch insert
                        if (isset($usedNoLabs[$noLab])) {
                            $failedCount++;
                            $this->importErrors[] = [
                                'type' => 'duplicate_lab',
                                'row' => $row['row_number'],
                                'message' => "No Lab '{$noLab}' duplikat dengan baris {$usedNoLabs[$noLab]} dalam file.",
                                'suggestion' => "Pastikan No Lab unik untuk setiap peserta.",
                            ];
                            continue;
                        }
                        $usedNoLabs[$noLab] = $row['row_number'];
                    }
                    
                    // Kumpulkan untuk batch insert
                    $insertData = $this->prepareParticipantData($data);
                    $insertData['diupload_oleh'] = $currentUserId;
                    $insertData['created_at'] = now();
                    $insertData['updated_at'] = now();
                    $toInsert[] = $insertData;
                }
            }
            
            // ========== EXECUTE BATCH OPERATIONS ==========
            
            // Batch update
            foreach ($toUpdate as $item) {
                try {
                    $item['existing']->update($item['data']);
                    $successCount++;
                } catch (\Exception $e) {
                    $failedCount++;
                    Log::error('Update failed', ['error' => $e->getMessage()]);
                }
            }
            
            // Batch insert menggunakan chunks
            if (!empty($toInsert)) {
                $chunks = array_chunk($toInsert, 100); // Insert 100 records at a time
                foreach ($chunks as $chunk) {
                    try {
                        Peserta::insert($chunk);
                        $successCount += count($chunk);
                    } catch (\Illuminate\Database\QueryException $e) {
                        // Jika batch insert gagal, coba insert satu per satu
                        foreach ($chunk as $record) {
                            try {
                                Peserta::create($record);
                                $successCount++;
                            } catch (\Exception $innerE) {
                                if ($this->duplicationStrategy === 'skip') {
                                    $skippedCount++;
                                } else {
                                    $failedCount++;
                                }
                            }
                        }
                    }
                }
            }

            $this->unggahan->update([
                'status' => 'imported',
                'baris_sukses' => $successCount,
                'baris_gagal' => $failedCount,
            ]);

            Log::info('Import completed', [
                'success' => $successCount,
                'skipped' => $skippedCount,
                'failed' => $failedCount,
            ]);

            session()->flash('toast', [
                'type' => 'success',
                'message' => "Berhasil import {$successCount} data peserta" . ($skippedCount > 0 ? ", {$skippedCount} dilewati" : ""),
            ]);

            $this->redirect(route('participants.index'), navigate: true);

        } catch (\Illuminate\Database\QueryException $e) {
            // Handle database errors dengan pesan user-friendly
            $errorCode = $e->getCode();
            $errorMessage = $e->getMessage();
            
            // Log full error ke console/log
            Log::error('Database error saat import', [
                'error_code' => $errorCode,
                'error_message' => $errorMessage,
                'sql' => $e->getSql() ?? 'N/A',
            ]);
            
            // Tampilkan pesan user-friendly berdasarkan error code
            if ($errorCode == 23000) {
                // Duplicate entry
                if (preg_match("/Duplicate entry '(.+?)' for key/", $errorMessage, $matches)) {
                    $duplicateValue = $matches[1] ?? 'unknown';
                    $userMessage = "Data duplikat terdeteksi: NRP/NIP '{$duplicateValue}' dengan tanggal periksa yang sama sudah ada di database.";
                    $suggestion = "Gunakan opsi 'Update' jika ingin memperbarui data lama.";
                } else {
                    $userMessage = "Data duplikat terdeteksi. NRP/NIP dengan tanggal periksa yang sama sudah ada di database.";
                    $suggestion = "Gunakan opsi 'Update' jika ingin memperbarui data lama.";
                }
            } elseif ($errorCode == 22001) {
                $userMessage = "Ada data yang terlalu panjang melebihi batas karakter.";
                $suggestion = "Periksa kolom yang memiliki teks sangat panjang.";
            } elseif ($errorCode == 22007) {
                $userMessage = "Format tanggal tidak valid.";
                $suggestion = "Gunakan format TANGGAL/BULAN/TAHUN (contoh: 20/01/2025).";
            } else {
                $userMessage = "Terjadi error database saat menyimpan data.";
                $suggestion = "Silakan cek format data dan coba lagi.";
            }
            
            // Simpan error ke property untuk ditampilkan di panel
            $this->importErrors[] = [
                'type' => 'database',
                'code' => $errorCode,
                'message' => $userMessage,
                'suggestion' => $suggestion ?? null,
            ];
            
        } catch (Exception $e) {
            // Handle general exceptions
            Log::error('Commit import failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            // Simpan error ke property untuk ditampilkan di panel
            $this->importErrors[] = [
                'type' => 'general',
                'code' => 'ERR',
                'message' => "Gagal import data.",
                'suggestion' => "Silakan periksa format file Excel dan coba lagi.",
            ];
        }
    }

    public function cancel(): void
    {
        $this->reset('uploadFile', 'parsedData', 'previewMode', 'unggahan', 'duplicationStrategy');
        $this->duplicationStrategy = 'skip';
        $this->categorizedValid = [];
        $this->categorizedWarning = [];
        $this->categorizedInvalid = [];
        $this->validationWarnings = [];
        $this->importErrors = [];
        $this->dispatch('show-toast', type: 'info', message: 'Import dibatalkan');
    }

    /**
     * Prepare participant data untuk database insert/update
     */
    protected function prepareParticipantData(array $data): array
    {
        return [
            'nama' => $data['nama'] ?? null,
            'pangkat' => $data['pangkat'] ?? null,
            'nrp_nip' => $data['nrp_nip'] ?? null,
            'jabatan' => $data['jabatan'] ?? null,
            'satuan_kerja' => $data['satuan_kerja'] ?? null,
            'no_hp_raw' => $data['no_hp_raw'] ?? null,
            'no_hp_wa' => $data['no_hp_wa'] ?? null,
            'tanggal_lahir' => $data['tanggal_lahir'] ?? null,
            'jenis_kelamin' => $data['jenis_kelamin'] ?? null,
            'no_lab' => $data['no_lab'] ?? null,
            'tanggal_periksa' => $data['tanggal_periksa'] ?? null,
            'kode_paket' => $data['kode_paket'] ?? null,
            'kode_instansi' => $data['kode_instansi'] ?? null,
        ];
    }

    public function getValidRowsProperty(): array
    {
        return $this->parsedData['valid'] ?? [];
    }

    public function getInvalidRowsProperty(): array
    {
        return $this->parsedData['invalid'] ?? [];
    }

    public function getTotalRowsProperty(): int
    {
        return count($this->categorizedValid) + count($this->categorizedWarning) + count($this->categorizedInvalid);
    }

    public function getValidCountProperty(): int
    {
        return count($this->categorizedValid);
    }
    
    public function getWarningCountProperty(): int
    {
        return count($this->categorizedWarning);
    }

    public function getInvalidCountProperty(): int
    {
        return count($this->categorizedInvalid);
    }
    
    /**
     * Data yang bisa diimport = valid + warning
     */
    public function getImportableRowsProperty(): array
    {
        return array_merge($this->categorizedValid, $this->categorizedWarning);
    }
    
    public function getImportableCountProperty(): int
    {
        return count($this->categorizedValid) + count($this->categorizedWarning);
    }

    /**
     * Hook when page number changes (pagination)
     */
    public function updatedPage()
    {
        $this->dispatch('table-loaded');
    }

    /**
     * Switch tab preview
     */
    public function setTab($tab)
    {
        if (in_array($tab, ['valid', 'warning', 'invalid'])) {
            $this->activeTab = $tab;
            $this->resetPage(); // Reset ke halaman 1 saat ganti tab
            $this->dispatch('table-loaded'); // Notify Alpine that table is ready
        }
    }

    public function render()
    {
        // Siapkan data untuk tab yang aktif
        $sourceData = [];
        switch ($this->activeTab) {
            case 'valid':
                $sourceData = $this->categorizedValid;
                break;
            case 'warning':
                $sourceData = $this->categorizedWarning;
                break;
            case 'invalid':
                $sourceData = $this->categorizedInvalid;
                break;
        }

        // Manual Pagination untuk Array
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 10;
        $currentItems = array_slice($sourceData, ($currentPage - 1) * $perPage, $perPage);
        $paginatedItems = new LengthAwarePaginator(
            $currentItems,
            count($sourceData),
            $perPage,
            $currentPage,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );

        return view('livewire.uploads.data-import', [
            'rows' => $paginatedItems
        ]);
    }
}
