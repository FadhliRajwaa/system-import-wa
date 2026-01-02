<?php

namespace App\Livewire\Settings\Paket;

use App\Models\Paket;
use App\Models\Peserta;
use Livewire\Component;
use Livewire\WithPagination;
use Flux\Flux;

class Index extends Component
{
    use WithPagination;

    public string $sortField = 'kode';
    public string $sortDirection = 'asc';
    public string $search = '';
    
    // Delete confirmation state
    public bool $showDeleteModal = false;
    public ?int $deletingPaketId = null;
    public string $deletingPaketNama = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
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

    public function confirmDelete($id): void
    {
        $paket = Paket::find($id);
        if ($paket) {
            $this->deletingPaketId = $id;
            $this->deletingPaketNama = $paket->nama;
            $this->showDeleteModal = true;
        }
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->resetDelete();
    }

    public function deleteConfirmed(): void
    {
        if (!$this->deletingPaketId) {
            return;
        }

        $paket = Paket::findOrFail($this->deletingPaketId);

        if ($paket->peserta()->exists()) {
            $this->showDeleteModal = false;
            $this->dispatch('show-toast', type: 'error', message: 'Tidak dapat menghapus paket yang masih digunakan peserta');
            $this->resetDelete();
            return;
        }

        $paket->delete();
        $this->showDeleteModal = false;
        $this->dispatch('show-toast', type: 'success', message: 'Paket berhasil dihapus');
        $this->resetDelete();
    }

    public function resetDelete(): void
    {
        $this->deletingPaketId = null;
        $this->deletingPaketNama = '';
    }

    public function render()
    {
        $paketList = Paket::query()
            ->when($this->search, fn($q) => $q->where('nama', 'like', "%{$this->search}%")
                ->orWhere('kode', 'like', "%{$this->search}%"))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(15);

        // Hitung total peserta yang terdaftar di paket yang ada
        $kodePaketList = Paket::pluck('kode')->toArray();
        $totalPeserta = Peserta::whereIn('kode_paket', $kodePaketList)->count();

        return view('livewire.settings.paket.index', [
            'paketList' => $paketList,
            'totalPeserta' => $totalPeserta,
        ]);
    }
}
