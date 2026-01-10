<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TemplateExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths
{
    public function array(): array
    {
        // Sample data rows sesuai format Tuan Fadhli
        return [
            [
                'AGUS YULIANTO',
                'IPTU',
                '72070427',
                'PAURMIN BAGSDM POLRESTA TANGERANG',
                'POLRES TANGERANG',
                '082294473921',
                '03/07/1973',
                'Pria',
                '25012025002',
                '20/01/2025',
                'INTENSIF I',
                '0002',
            ],
            [
                'ROBBY SABILLY',
                'AIPDA',
                '85050716',
                'BAMIN SIPROPAM POLRESTA TANGERANG',
                'POLRES TANGERANG',
                '087883252293',
                '26/05/1985',
                'Pria',
                '25012025003',
                '25/01/2025',
                'INTENSIF II',
                '0002',
            ],
            [
                'SUGENG PRANOWO',
                'AIPTU',
                '81040471',
                'PAUR SUBBAG BEKFAI',
                'POLRES TANGERANG',
                '081311860516',
                '25/04/1961',
                'Wanita',
                '25012025004',
                '25/04/1981',
                'INTENSIF III',
                '0002',
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'Nama',                          // A - Wajib
            'Pangkat',                       // B
            'NRP/NIP',                       // C - Wajib
            'Jabatan',                       // D
            'satuan_kerja',                  // E
            'no_hp',                         // F - Wajib
            'tgl_lahir(dd/mm/yyyy)',         // G
            'gender(Pria/Wanita)',           // H
            'no_lab',                        // I
            'tgl_pemeriksaan(dd/mm/yyyy)',   // J - Wajib
            'kode_paket',                    // K
            'kode_perusahaan',               // L
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25,  // Nama
            'B' => 10,  // Pangkat
            'C' => 15,  // NRP/NIP
            'D' => 40,  // Jabatan
            'E' => 25,  // satuan_kerja
            'F' => 15,  // no_hp
            'G' => 22,  // tgl_lahir
            'H' => 20,  // gender
            'I' => 15,  // no_lab
            'J' => 25,  // tgl_pemeriksaan
            'K' => 15,  // kode_paket
            'L' => 18,  // kode_perusahaan
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Header row bold with background
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFE0E0E0'],
                ],
            ],
        ];
    }
}
