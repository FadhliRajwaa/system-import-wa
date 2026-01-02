<?php

namespace App\Exports;

use App\Models\Peserta;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ParticipantsExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    public function query()
    {
        $user = Auth::user();
        
        return Peserta::query()
            ->when(!$user->isAdmin(), fn ($query) => $query->where('diupload_oleh', $user->id))
            ->orderBy('tanggal_periksa', 'desc');
    }

    public function headings(): array
    {
        return [
            'Nama',
            'Pangkat',
            'NRP/NIP',
            'Jabatan',
            'Satuan Kerja',
            'No HP',
            'Tgl Lahir',
            'Jenis Kelamin',
            'No Lab',
            'Tgl Periksa',
            'Kode Paket',
            'Kode Instansi',
        ];
    }

    public function map($peserta): array
    {
        return [
            $peserta->nama,
            $peserta->pangkat,
            $peserta->nrp_nip,
            $peserta->jabatan,
            $peserta->satuan_kerja,
            $peserta->no_hp_raw,
            $peserta->tanggal_lahir?->format('d/m/Y'),
            $peserta->jenis_kelamin,
            $peserta->no_lab,
            $peserta->tanggal_periksa?->format('d/m/Y'),
            $peserta->kode_paket,
            $peserta->kode_instansi,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 11]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25,
            'B' => 15,
            'C' => 15,
            'D' => 30,
            'E' => 25,
            'F' => 18,
            'G' => 12,
            'H' => 12,
            'I' => 15,
            'J' => 12,
            'K' => 15,
            'L' => 15,
        ];
    }
}
