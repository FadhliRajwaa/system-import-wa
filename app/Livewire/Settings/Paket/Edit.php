<?php

namespace App\Livewire\Settings\Paket;

use App\Models\Paket;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Edit extends Component
{
    public Paket $paket;
    public string $kode = '';
    public string $nama = '';

    public function mount(Paket $paket): void
    {
        $this->paket = $paket;
        $this->kode = $paket->kode;
        $this->nama = $paket->nama;
    }

    public function rules(): array
    {
        return [
            'kode' => ['required', 'string', 'max:50', Rule::unique('paket', 'kode')->ignore($this->paket->id)],
            'nama' => 'required|string|max:255',
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        $this->paket->update([
            'kode' => $validated['kode'],
            'nama' => $validated['nama'],
        ]);

        $this->dispatch('show-toast', type: 'success', message: 'Paket berhasil diperbarui');
        $this->redirect(route('settings.paket'), navigate: true);
    }

    public function cancel(): void
    {
        $this->redirect(route('settings.paket'), navigate: true);
    }

    public function render()
    {
        return view('livewire.settings.paket.edit');
    }
}
