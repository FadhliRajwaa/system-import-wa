<?php

namespace App\Livewire\Dashboard;

use App\Models\Peserta;
use App\Models\Unggahan;
use App\Models\PesanWa;
use App\Models\Paket;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Computed;

class Index extends Component
{
    public int $selectedYear;
    public array $availableYears = [];

    public function mount(): void
    {
        Carbon::setLocale('id');

        // Get available years from data
        $this->availableYears = $this->getAvailableYears();
        $this->selectedYear = $this->availableYears[0] ?? now()->year;
    }

    protected function getAvailableYears(): array
    {
        $user = Auth::user();

        // Get registered paket codes
        $registeredPaketCodes = Paket::pluck('kode')->toArray();

        $query = DB::table('peserta')
            ->selectRaw('DISTINCT YEAR(tanggal_periksa) as tahun')
            ->whereIn('kode_paket', $registeredPaketCodes);

        if (!$user->isAdmin()) {
            $query->where('diupload_oleh', $user->id);
        }

        return $query->orderByDesc('tahun')
            ->pluck('tahun')
            ->toArray();
    }

    public function updatedSelectedYear(): void
    {
        $this->dispatch('chart-data-updated',
            paketData: $this->getChartPaketTahunan(),
            tahunData: $this->getChartPerTahun()
        );
    }
    /**
     * Get the base query for peserta based on user role.
     */
    protected function getPesertaQuery(): Builder
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return Peserta::query();
        }

        return Peserta::forUser($user->id);
    }

    /**
     * Get the base query for peserta filtered by registered paket codes.
     */
    protected function getFilteredPesertaQuery(): Builder
    {
        $registeredPaketCodes = Paket::pluck('kode')->toArray();
        return $this->getPesertaQuery()->whereIn('kode_paket', $registeredPaketCodes);
    }

    /**
     * Get the base query for pesan_wa based on user role.
     */
    protected function getPesanWaQuery(): Builder
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return PesanWa::query();
        }

        return PesanWa::whereHas('peserta', function ($query) use ($user) {
            $query->where('diupload_oleh', $user->id);
        });
    }

    public function getTotalPesertaProperty(): int
    {
        // Show all peserta without paket filter for accurate total
        return $this->getPesertaQuery()->count();
    }

    public function getSentWaProperty(): int
    {
        return $this->getPesertaQuery()->where('status_wa', 'sent')->count();
    }

    public function getNotSentWaProperty(): int
    {
        return $this->getPesertaQuery()->where('status_wa', 'not_sent')->count();
    }

    public function getHasPdfProperty(): int
    {
        return $this->getPesertaQuery()->where('status_pdf', 'uploaded')->count();
    }

    public function getNoPdfProperty(): int
    {
        return $this->getPesertaQuery()->where('status_pdf', 'not_uploaded')->count();
    }

    public function getLastUploadProperty(): ?Unggahan
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return Unggahan::query()->latest()->first();
        }

        return Unggahan::where('diupload_oleh', $user->id)->latest()->first();
    }

    public function getRecentPesertaProperty()
    {
        return $this->getPesertaQuery()
            ->with(['paket', 'instansi', 'uploader'])
            ->latest()
            ->limit(5)
            ->get();
    }

    public function getWaStatsProperty(): array
    {
        return [
            'success' => $this->getPesanWaQuery()->where('status', 'success')->count(),
            'belum_kirim' => $this->getPesanWaQuery()->where('status', 'belum_kirim')->count(),
        ];
    }

    public function getChartData(): array
    {
        return [
            'labels' => ['Terkirim', 'Belum Kirim', 'Ada PDF'],
            'data' => [
                $this->sentWa,
                $this->notSentWa,
                $this->hasPdf,
            ],
            'colors' => ['bg-green-500', 'bg-gray-400', 'bg-teal-500'],
        ];
    }

    /**
     * Get chart data for "Laporan Per Paket Tahunan"
     * Only shows pakets that are registered in the paket table
     */
    public function getChartPaketTahunan(): array
    {
        $user = Auth::user();

        // Get registered paket codes
        $registeredPaketCodes = Paket::pluck('kode')->toArray();

        $query = DB::table('peserta')
            ->selectRaw('kode_paket, COUNT(*) as jumlah')
            ->whereYear('tanggal_periksa', $this->selectedYear)
            ->whereNotNull('kode_paket')
            ->whereIn('kode_paket', $registeredPaketCodes)
            ->groupBy('kode_paket')
            ->orderBy('kode_paket');

        if (!$user->isAdmin()) {
            $query->where('diupload_oleh', $user->id);
        }

        $data = $query->get();

        // Generate beautiful gradient colors
        $colors = [
            ['bg' => 'rgba(30, 64, 175, 0.9)', 'border' => 'rgb(30, 64, 175)'],      // Blue-800
            ['bg' => 'rgba(245, 158, 11, 0.9)', 'border' => 'rgb(245, 158, 11)'],   // Amber-500
            ['bg' => 'rgba(16, 185, 129, 0.9)', 'border' => 'rgb(16, 185, 129)'],   // Emerald-500
            ['bg' => 'rgba(99, 102, 241, 0.9)', 'border' => 'rgb(99, 102, 241)'],   // Indigo-500
            ['bg' => 'rgba(236, 72, 153, 0.9)', 'border' => 'rgb(236, 72, 153)'],   // Pink-500
            ['bg' => 'rgba(14, 165, 233, 0.9)', 'border' => 'rgb(14, 165, 233)'],   // Sky-500
            ['bg' => 'rgba(168, 85, 247, 0.9)', 'border' => 'rgb(168, 85, 247)'],   // Purple-500
            ['bg' => 'rgba(239, 68, 68, 0.9)', 'border' => 'rgb(239, 68, 68)'],     // Red-500
        ];

        $bgColors = [];
        $borderColors = [];
        foreach ($data as $index => $item) {
            $colorIndex = $index % count($colors);
            $bgColors[] = $colors[$colorIndex]['bg'];
            $borderColors[] = $colors[$colorIndex]['border'];
        }

        return [
            'labels' => $data->pluck('kode_paket')->toArray(),
            'data' => $data->pluck('jumlah')->toArray(),
            'backgroundColor' => $bgColors,
            'borderColor' => $borderColors,
        ];
    }

    /**
     * Get chart data for "Laporan Per Tahun"
     * Only counts peserta with registered paket codes
     */
    public function getChartPerTahun(): array
    {
        $user = Auth::user();

        // Get registered paket codes
        $registeredPaketCodes = Paket::pluck('kode')->toArray();

        $query = DB::table('peserta')
            ->selectRaw('YEAR(tanggal_periksa) as tahun, COUNT(*) as jumlah')
            ->whereNotNull('tanggal_periksa')
            ->whereIn('kode_paket', $registeredPaketCodes)
            ->groupBy(DB::raw('YEAR(tanggal_periksa)'))
            ->orderBy('tahun');

        if (!$user->isAdmin()) {
            $query->where('diupload_oleh', $user->id);
        }

        $data = $query->get();

        // Teal gradient for yearly chart
        $bgColors = array_fill(0, count($data), 'rgba(20, 184, 166, 0.9)');
        $borderColors = array_fill(0, count($data), 'rgb(20, 184, 166)');

        return [
            'labels' => $data->pluck('tahun')->toArray(),
            'data' => $data->pluck('jumlah')->toArray(),
            'backgroundColor' => $bgColors,
            'borderColor' => $borderColors,
        ];
    }

    public function render()
    {
        return view('livewire.dashboard.index', [
            'totalPeserta' => $this->totalPeserta,
            'sentWa' => $this->sentWa,
            'notSentWa' => $this->notSentWa,
            'hasPdf' => $this->hasPdf,
            'noPdf' => $this->noPdf,
            'lastUpload' => $this->lastUpload,
            'recentPeserta' => $this->recentPeserta,
            'waStats' => $this->waStats,
            'chartData' => $this->getChartData(),
            'isAdmin' => Auth::user()->isAdmin(),
            'chartPaketTahunan' => $this->getChartPaketTahunan(),
            'chartPerTahun' => $this->getChartPerTahun(),
        ]);
    }
}
