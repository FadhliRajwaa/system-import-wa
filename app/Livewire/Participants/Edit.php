<?php

namespace App\Livewire\Participants;

use App\Models\Peserta;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Edit extends Component
{
    public $nrp_nip;
    public $tanggal_periksa;
    public $data = [];

    // Props from URL
    public function mount($nrp_nip, $tanggal_periksa)
    {
        $this->nrp_nip = $nrp_nip;
        // Tanggal periksa in URL is Y-m-d, matching database format
        $this->tanggal_periksa = $tanggal_periksa;

        // Gunakan findByCompositeKey untuk konsistensi
        $peserta = Peserta::findByCompositeKey($this->nrp_nip, $this->tanggal_periksa);
        
        if (!$peserta) {
            abort(404, 'Data peserta tidak ditemukan');
        }

        $this->data = [
            'nama' => $peserta->nama,
            'pangkat' => $peserta->pangkat ?? '',
            'jabatan' => $peserta->jabatan ?? '',
            'satuan_kerja' => $peserta->satuan_kerja ?? '',
            'no_hp_raw' => $peserta->no_hp_raw ?? '',
            'no_lab' => $peserta->no_lab ?? '',
            'kode_paket' => $peserta->kode_paket ?? '',
            'kode_instansi' => $peserta->kode_instansi ?? '',
            'tanggal_lahir' => $peserta->tanggal_lahir ? $peserta->tanggal_lahir->format('Y-m-d') : '',
        ];
    }

    public function update()
    {
        // Gunakan findByCompositeKey untuk konsistensi
        $peserta = Peserta::findByCompositeKey($this->nrp_nip, $this->tanggal_periksa);
        
        if (!$peserta) {
            session()->flash('toast', [
                'type' => 'error',
                'message' => 'Data peserta tidak ditemukan',
            ]);
            return redirect()->route('participants.index');
        }

        $validated = $this->validate([
            'data.nama' => 'required|string|max:255',
            'data.pangkat' => 'nullable|string|max:100',
            'data.jabatan' => 'nullable|string|max:255',
            'data.satuan_kerja' => 'nullable|string|max:255',
            'data.no_hp_raw' => 'nullable|string|max:50',
            'data.no_lab' => 'nullable|string|max:50',
            'data.kode_paket' => 'nullable|string|max:50',
            'data.kode_instansi' => 'nullable|string|max:50',
            'data.tanggal_lahir' => 'nullable|date',
        ]);

        // Format no_hp_wa dari no_hp_raw (konversi ke format WhatsApp)
        $noHpWa = $this->formatPhoneToWhatsApp($this->data['no_hp_raw']);

        $peserta->nama = $this->data['nama'];
        $peserta->pangkat = $this->data['pangkat'] ?: null;
        $peserta->jabatan = $this->data['jabatan'] ?: null;
        $peserta->satuan_kerja = $this->data['satuan_kerja'] ?: null;
        $peserta->no_hp_raw = $this->data['no_hp_raw'] ?: null;
        $peserta->no_hp_wa = $noHpWa;
        $peserta->no_lab = $this->data['no_lab'] ?: null;
        $peserta->kode_paket = $this->data['kode_paket'] ?: null;
        $peserta->kode_instansi = $this->data['kode_instansi'] ?: null;
        $peserta->tanggal_lahir = $this->data['tanggal_lahir'] ?: null;
        
        $saved = $peserta->save();
        
        if ($saved) {
            session()->flash('toast', [
                'type' => 'success',
                'message' => 'Data peserta berhasil diperbarui',
            ]);
        } else {
            session()->flash('toast', [
                'type' => 'error',
                'message' => 'Gagal menyimpan perubahan',
            ]);
        }

        return redirect()->route('participants.index');
    }
    
    /**
     * Format nomor HP ke format WhatsApp (62xxx)
     */
    private function formatPhoneToWhatsApp(?string $phone): ?string
    {
        if (empty($phone)) {
            return null;
        }
        
        // Hapus semua karakter non-digit
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Jika dimulai dengan 0, ganti dengan 62
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }
        // Jika dimulai dengan 8, tambah 62 di depan
        elseif (str_starts_with($phone, '8')) {
            $phone = '62' . $phone;
        }
        // Jika sudah dimulai dengan 62, biarkan
        
        return $phone;
    }

    public function cancel()
    {
        return redirect()->route('participants.index');
    }

    public function render()
    {
        return view('livewire.participants.edit');
    }
}
