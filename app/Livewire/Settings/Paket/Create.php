<?php

namespace App\Livewire\Settings\Paket;

use App\Models\Paket;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Create extends Component
{
    public string $kode = '';
    public string $nama = '';

    public function rules(): array
    {
        return [
            'kode' => ['required', 'string', 'max:50', Rule::unique('paket', 'kode')],
            'nama' => 'required|string|max:255',
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        Paket::create([
            'kode' => $validated['kode'],
            'nama' => $validated['nama'],
        ]);

        $this->dispatch('show-toast', type: 'success', message: 'Paket berhasil ditambahkan');
        $this->redirect(route('settings.paket'), navigate: true);
    }

    public function cancel(): void
    {
        $this->redirect(route('settings.paket'), navigate: true);
    }

    public function render()
    {
        return view('livewire.settings.paket.create');
    }
}
