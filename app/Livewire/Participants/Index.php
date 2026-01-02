<?php

namespace App\Livewire\Participants;

use App\Exports\ParticipantsExport;
use App\Models\Peserta;
use App\Models\PesanWa;
use App\Models\User;
use App\Services\PesanWaService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class Index extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    public array $filters = [
        'tanggal_mulai' => null,
        'tanggal_akhir' => null,
        'kode_paket' => null,
        'satuan_kerja' => null, // Changes: Replaced kode_instansi with satuan_kerja
        'status_wa' => null,
        'status_pdf' => null,
    ];

    public string $sortField = 'tanggal_periksa';

    public string $sortDirection = 'desc';

    public array $selectedRows = [];

    public bool $selectAll = false;
    
    #[Url(except: 10)]
    public int $perPage = 10;

    // Properties untuk Delete Confirmation
    public bool $showDeleteModal = false;
    public ?string $deleteNrpNip = null;
    public ?string $deleteTanggalPeriksa = null;
    public ?string $deleteNama = null;

    public function getPesertaProperty()
    {
        /** @var User $user */
        $user = Auth::user();
        
        return Peserta::query()
            ->with(['paket', 'instansi', 'uploader'])
            // Role-based filtering: User only sees their own data
            ->when(!$user->isAdmin(), fn ($query) => $query->where('diupload_oleh', $user->id))
            ->when($this->filters['tanggal_mulai'], fn ($query) => $query->whereDate('tanggal_periksa', '>=', $this->filters['tanggal_mulai']))
            ->when($this->filters['tanggal_akhir'], fn ($query) => $query->whereDate('tanggal_periksa', '<=', $this->filters['tanggal_akhir']))
            ->when($this->filters['kode_paket'], fn ($query) => $query->where('kode_paket', $this->filters['kode_paket']))
            ->when($this->filters['satuan_kerja'], fn ($query) => $query->where('satuan_kerja', $this->filters['satuan_kerja']))
            ->when($this->filters['status_wa'], fn ($query) => $query->where('status_wa', $this->filters['status_wa']))
            ->when($this->filters['status_pdf'], fn ($query) => $query->where('status_pdf', $this->filters['status_pdf']))
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('nama', 'like', "%{$this->search}%")
                        ->orWhere('nrp_nip', 'like', "%{$this->search}%")
                        ->orWhere('no_lab', 'like', "%{$this->search}%")
                        ->orWhere('no_hp_raw', 'like', "%{$this->search}%");
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage === -1 ? 9999 : $this->perPage);
    }

    public function getSatuanKerjaListProperty(): array
    {
        /** @var User $authUser */
        $authUser = Auth::user();
        return Peserta::query()
            ->when(!$authUser->isAdmin(), fn ($query) => $query->where('diupload_oleh', Auth::id()))
            ->whereNotNull('satuan_kerja')
            ->distinct()
            ->orderBy('satuan_kerja')
            ->pluck('satuan_kerja')
            ->toArray();
    }

    public function getPaketListProperty(): array
    {
        /** @var User $authUser */
        $authUser = Auth::user();
        return Peserta::query()
            ->when(!$authUser->isAdmin(), fn ($query) => $query->where('diupload_oleh', Auth::id()))
            ->whereNotNull('kode_paket')
            ->distinct()
            ->pluck('kode_paket')
            ->toArray();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function resetFilters(): void
    {
        $this->reset('search', 'filters');
    }

    public function toggleSelectAll(): void
    {
        if ($this->selectAll) {
            // Jika sudah select all, unselect semua
            $this->selectedRows = [];
            $this->selectAll = false;
        } else {
            // Select semua di halaman saat ini
            $this->selectedRows = $this->peserta->getCollection()
                ->map(fn ($item) => $item->nrp_nip . '|' . $item->tanggal_periksa->format('Y-m-d'))
                ->toArray();
            $this->selectAll = true;
        }
    }

    public function updatedFilters(): void
    {
        $this->resetPage();
        $this->selectedRows = [];
        $this->selectAll = false;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
        $this->selectedRows = [];
        $this->selectAll = false;
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
        $this->selectedRows = [];
        $this->selectAll = false;
    }

    public function updatedSelectAll($value): void
    {
        if ($value) {
            $this->selectedRows = $this->peserta->getCollection()
                ->map(fn ($item) => $item->nrp_nip . '|' . $item->tanggal_periksa->format('Y-m-d'))
                ->toArray();
        } else {
            $this->selectedRows = [];
        }
    }

    public function toggleRow(string $id): void
    {
        if (in_array($id, $this->selectedRows)) {
            $this->selectedRows = array_values(array_diff($this->selectedRows, [$id]));
        } else {
            $this->selectedRows[] = $id;
        }
        
        // Update selectAll state based on current page items
        $currentPageIds = $this->peserta->getCollection()
            ->map(fn ($item) => $item->nrp_nip . '|' . $item->tanggal_periksa->format('Y-m-d'))
            ->toArray();
            
        $allCurrentPageSelected = count(array_intersect($currentPageIds, $this->selectedRows)) === count($currentPageIds);
        $this->selectAll = $allCurrentPageSelected && count($currentPageIds) > 0;
    }

    public function queueBulkWa(): void
    {
        // Resolve service internally (Livewire actions don't support method injection)
        $waService = app(PesanWaService::class);
        
        if (empty($this->selectedRows)) {
            $this->dispatch('show-toast', type: 'error', message: 'Pilih minimal satu peserta');
            return;
        }

        /** @var User $user */
        $user = Auth::user();
        
        // Build query using composite keys
        $query = Peserta::query()
            ->with(['paket', 'instansi']) // Eager load for message generation
            ->when(!$user->isAdmin(), fn ($q) => $q->where('diupload_oleh', $user->id));

        $query->where(function($q) {
            foreach ($this->selectedRows as $row) {
                // Parse key: "nrp_nip|YYYY-MM-DD"
                $parts = explode('|', $row);
                if (count($parts) === 2) {
                    $nrp = $parts[0];
                    $date = $parts[1];
                    $q->orWhere(function($sub) use ($nrp, $date) {
                        $sub->where('nrp_nip', $nrp)
                            ->whereRaw('DATE(tanggal_periksa) = ?', [$date]);
                    });
                }
            }
        });

        $allSelected = $query->get();

        // Filter eligible participants
        $pesertaEligible = $allSelected->filter(function ($peserta) {
            // Must have phone number
            if (empty($peserta->no_hp_wa)) {
                return false;
            }
            // Must have PDF uploaded
            if ($peserta->status_pdf !== 'uploaded') {
                return false;
            }
            // Skip already sent
            if ($peserta->status_wa === 'success') {
                return false;
            }
            return true;
        });

        if ($pesertaEligible->isEmpty()) {
            $reasons = [];
            $noPhone = $allSelected->filter(fn($p) => empty($p->no_hp_wa))->count();
            $noPdf = $allSelected->filter(fn($p) => $p->status_pdf !== 'uploaded')->count();
            $alreadySent = $allSelected->filter(fn($p) => $p->status_wa === 'sent')->count();
            
            if ($noPhone > 0) $reasons[] = "{$noPhone} tanpa no HP";
            if ($noPdf > 0) $reasons[] = "{$noPdf} belum upload PDF";
            if ($alreadySent > 0) $reasons[] = "{$alreadySent} sudah terkirim";
            
            $message = 'Tidak ada peserta yang bisa dikirim WA';
            if (!empty($reasons)) {
                $message .= ' (' . implode(', ', $reasons) . ')';
            }
            
            $this->dispatch('show-toast', type: 'warning', message: $message);
            return;
        }

        $successCount = 0;
        $failCount = 0;
        $waUrls = []; // Collect URLs for manual mode

        foreach ($pesertaEligible as $peserta) {
            // Create record
            $pesan = PesanWa::create([
                'provider' => config('services.whatsapp.provider', 'manual'),
                'no_tujuan' => $peserta->no_hp_wa,
                'isi_pesan' => $this->generateWaMessage($peserta),
                'status' => 'belum_kirim',
                'percobaan' => 0,
                'nrp_nip_peserta' => $peserta->nrp_nip,
                'dibuat_oleh' => Auth::id(),
            ]);

            // Send based on provider
            $result = $waService->sendNow($pesan, $peserta);

            if ($result['success']) {
                $successCount++;
                // Collect URL for manual mode
                if (isset($result['mode']) && $result['mode'] === 'manual' && isset($result['url'])) {
                    $waUrls[] = $result['url'];
                }
            } else {
                $failCount++;
            }
        }

        $this->selectedRows = [];
        $this->selectAll = false;
        
        // Handle different modes
        if (!empty($waUrls)) {
            // Manual mode: dispatch event to open URLs
            $this->dispatch('open-wa-links', urls: $waUrls);
            $this->dispatch('show-toast', type: 'info', message: "Membuka {$successCount} link WhatsApp...");
        } elseif ($failCount > 0) {
            $this->dispatch('show-toast', type: 'warning', message: "Selesai: {$successCount} terkirim, {$failCount} gagal.");
        } else {
            $this->dispatch('show-toast', type: 'success', message: "Selesai: {$successCount} pesan berhasil dikirim.");
        }
    }

    public function generateWaMessage(Peserta $peserta): string
    {
        // Ambil template dari instansi peserta, atau fallback ke DEFAULT
        $instansi = $peserta->instansi;

        // Jika instansi tidak ditemukan (kode_instansi kosong atau tidak terdaftar), gunakan DEFAULT
        if (!$instansi) {
            $instansi = \App\Models\Instansi::where('kode', 'DEFAULT')->first();
        }

        // Ambil template prolog dari instansi (atau kosong jika tidak ada)
        $template = $instansi?->template_prolog ?? '';

        // Jika template masih kosong, gunakan fallback minimal tanpa emoji
        if (empty(trim($template))) {
            $template = "Hasil pemeriksaan kesehatan: {{link}}";
        }

        // Strip HTML tags dari Quill editor dan decode entities
        $template = strip_tags($template);
        $template = html_entity_decode($template, ENT_QUOTES, 'UTF-8');

        // Normalize line breaks
        $template = str_replace(["\r\n", "\r"], "\n", $template);
        $template = preg_replace("/\n{3,}/", "\n\n", $template); // Max 2 newlines
        $template = trim($template);

        // Generate link PDF jika ada - menggunakan Storage::url() untuk kompatibilitas local/production
        $pdfLink = '';
        if ($peserta->sudahAdaPdf() && $peserta->path_pdf) {
            // Storage::disk('public')->url() akan menghasilkan URL yang benar
            // - Local: http://localhost:8000/storage/peserta-pdfs/xxx.pdf
            // - Production: https://domain.com/storage/peserta-pdfs/xxx.pdf
            // - S3/Cloud: https://s3.amazonaws.com/bucket/peserta-pdfs/xxx.pdf
            $pdfLink = \Illuminate\Support\Facades\Storage::disk('public')->url($peserta->path_pdf);
        }

        // Hitung tahun anggaran dari tanggal periksa
        $tahunAnggaran = $peserta->tanggal_periksa ? $peserta->tanggal_periksa->format('Y') : date('Y');

        // Ganti variabel placeholder dengan data peserta
        $message = str_replace([
            '{{waktu}}',
            '{{no_lab}}',
            '{{nama_pasien}}',
            '{{pangkat}}',
            '{{nrp}}',
            '{{satuan_kerja}}',
            '{{tahun_anggaran}}',
            '{{link}}'
        ], [
            $peserta->tanggal_periksa ? $peserta->tanggal_periksa->format('d/m/Y') : '-',
            $peserta->no_lab ?? '-',
            $peserta->nama ?? '-',
            $peserta->pangkat ?? '-',
            $peserta->nrp_nip ?? '-',
            $peserta->satuan_kerja ?? '-',
            $tahunAnggaran,
            $pdfLink
        ], $template);

        return trim($message);
    }

    public function exportData()
    {
        return Excel::download(new ParticipantsExport, 'data_peserta_' . date('Y-m-d_His') . '.xlsx');
    }

    public function openBulkWaModal(): void
    {
        // Jika tidak ada yang dipilih, select otomatis yang eligible
        if (empty($this->selectedRows)) {
            /** @var User $user */
            $user = Auth::user();
            $eligible = Peserta::query()
                ->when(!$user->isAdmin(), fn ($query) => $query->where('diupload_oleh', $user->id))
                ->where('status_pdf', 'uploaded')
                ->where('status_wa', '!=', 'success')
                ->whereNotNull('no_hp_wa')
                ->get() // Fetch eligible records first
                ->map(fn ($item) => $item->nrp_nip . '|' . $item->tanggal_periksa->format('Y-m-d'))
                ->toArray();
            
            if (!empty($eligible)) {
                $this->selectedRows = $eligible;
                $this->dispatch('show-toast', type: 'info', message: count($eligible) . ' peserta dipilih otomatis (PDF ready, belum kirim WA)');
            } else {
                $this->dispatch('show-toast', type: 'warning', message: 'Tidak ada peserta yang eligible untuk kirim WA');
                return;
            }
        }
        
        $this->queueBulkWa();
    }

    public function getStatusWaBadgeClassProperty(): array
    {
        return [
            'belum_kirim' => 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-200',
            'success' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400',
        ];
    }

    // ========== DELETE METHODS ==========
    
    // Property untuk bulk delete
    public bool $showBulkDeleteModal = false;
    public int $bulkDeleteCount = 0;

    public function confirmDelete(string $nrpNip, string $tanggalPeriksa): void
    {
        // Use DATE() for consistent comparison with Y-m-d string
        $peserta = Peserta::where('nrp_nip', $nrpNip)
            ->whereRaw('DATE(tanggal_periksa) = ?', [$tanggalPeriksa])
            ->first();

        if (!$peserta) {
            $this->dispatch('show-toast', type: 'error', message: 'Data peserta tidak ditemukan');
            return;
        }

        $this->deleteNrpNip = $nrpNip;
        $this->deleteTanggalPeriksa = $tanggalPeriksa;
        $this->deleteNama = $peserta->nama;
        $this->showDeleteModal = true;
    }

    public function deletePeserta(): void
    {
        // Use DATE() for consistent comparison
        $deleted = Peserta::where('nrp_nip', $this->deleteNrpNip)
            ->whereRaw('DATE(tanggal_periksa) = ?', [$this->deleteTanggalPeriksa])
            ->delete();

        if ($deleted) {
            $this->dispatch('show-toast', type: 'success', message: "Data {$this->deleteNama} berhasil dihapus");
        } else {
            $this->dispatch('show-toast', type: 'error', message: 'Gagal menghapus data');
        }

        $this->showDeleteModal = false;
        $this->resetDeleteData();
    }
    
    /**
     * Show confirmation modal for bulk delete
     */
    public function confirmDeleteSelected(): void
    {
        if (empty($this->selectedRows)) {
            $this->dispatch('show-toast', type: 'error', message: 'Pilih minimal satu peserta untuk dihapus');
            return;
        }
        
        $this->bulkDeleteCount = count($this->selectedRows);
        $this->showBulkDeleteModal = true;
    }
    
    /**
     * Delete all selected participants
     */
    public function deleteSelected(): void
    {
        if (empty($this->selectedRows)) {
            $this->dispatch('show-toast', type: 'error', message: 'Tidak ada data yang dipilih');
            return;
        }
        
        /** @var User $user */
        $user = Auth::user();
        
        $deletedCount = 0;
        
        foreach ($this->selectedRows as $row) {
            // Parse key: "nrp_nip|YYYY-MM-DD"
            $parts = explode('|', $row);
            if (count($parts) === 2) {
                $nrp = $parts[0];
                $date = $parts[1];
                
                $query = Peserta::where('nrp_nip', $nrp)
                    ->whereRaw('DATE(tanggal_periksa) = ?', [$date]);
                
                // Non-admin can only delete their own data
                if (!$user->isAdmin()) {
                    $query->where('diupload_oleh', $user->id);
                }
                
                $deleted = $query->delete();
                if ($deleted) {
                    $deletedCount++;
                }
            }
        }
        
        if ($deletedCount > 0) {
            $this->dispatch('show-toast', type: 'success', message: "{$deletedCount} data peserta berhasil dihapus");
        } else {
            $this->dispatch('show-toast', type: 'error', message: 'Tidak ada data yang berhasil dihapus');
        }
        
        $this->selectedRows = [];
        $this->selectAll = false;
        $this->showBulkDeleteModal = false;
        $this->bulkDeleteCount = 0;
    }
    
    public function closeBulkDeleteModal(): void
    {
        $this->showBulkDeleteModal = false;
        $this->bulkDeleteCount = 0;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->resetDeleteData();
    }

    private function resetDeleteData(): void
    {
        $this->deleteNrpNip = null;
        $this->deleteTanggalPeriksa = null;
        $this->deleteNama = null;
    }

    public function render()
    {
        \Carbon\Carbon::setLocale('id');
        
        return view('livewire.participants.index', [
            'pesertaList' => $this->peserta,
            'satuanKerjaList' => $this->satuanKerjaList,
            'paketList' => $this->paketList,
        ]);
    }
}
