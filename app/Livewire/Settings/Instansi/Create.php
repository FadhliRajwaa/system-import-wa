<?php

namespace App\Livewire\Settings\Instansi;

use App\Models\Instansi;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Create extends Component
{
    public string $kode = '';
    public string $nama = '';
    public string $templateProlog = '';

    public function rules(): array
    {
        return [
            'kode' => ['required', 'string', 'max:50', Rule::unique('instansi', 'kode')],
            'nama' => 'required|string|max:255',
            'templateProlog' => 'required|string|max:2000',
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        Instansi::create([
            'kode' => $validated['kode'],
            'nama' => $validated['nama'],
            'template_prolog' => $validated['templateProlog'],
        ]);

        $this->dispatch('show-toast', type: 'success', message: 'Instansi berhasil ditambahkan');
        $this->redirect(route('settings.instansi'), navigate: true);
    }

    public function cancel(): void
    {
        $this->redirect(route('settings.instansi'), navigate: true);
    }

    public function render()
    {
        return view('livewire.settings.instansi.create');
    }
}
