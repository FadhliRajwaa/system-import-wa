<?php

namespace App\Livewire\Settings\Instansi;

use App\Models\Instansi;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $sortField = 'kode';
    public string $sortDirection = 'asc';
    public string $search = '';

    public function getInstansiListProperty()
    {
        return Instansi::query()
            ->when($this->search, fn($q) => $q->where('nama', 'like', "%{$this->search}%")
                ->orWhere('kode', 'like', "%{$this->search}%"))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(15);
    }

    public function delete(int $id): void
    {
        $instansi = Instansi::findOrFail($id);

        if ($instansi->peserta()->exists()) {
            $this->dispatch('show-toast', type: 'error', message: 'Tidak dapat menghapus instansi yang masih digunakan peserta');
            return;
        }

        $instansi->delete();
        $this->dispatch('show-toast', type: 'success', message: 'Instansi berhasil dihapus');
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

    public function render()
    {
        return view('livewire.settings.instansi.index', [
            'instansiList' => $this->instansiList,
        ]);
    }
}
