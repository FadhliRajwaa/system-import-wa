<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Company>
 */
class CompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $counter = 1;

        return [
            'code' => 'INS'.str_pad((string) $counter++, 3, '0', STR_PAD_LEFT),
            'name' => fake()->randomElement([
                'PT Indo Sehat',
                'RS Umum Daerah',
                'PT Asuransi Kesehatan',
                'PT Bank Nasional',
                'PT Telekomunikasi Indonesia',
                'PT Pertamina',
                'PT PLN Persero',
                'PT Garuda Indonesia',
                'PT Angkasa Pura',
                'PT Pelindo',
            ]),
            'prolog_template' => 'Halo {name}, berikut hasil pemeriksaan Anda untuk paket {package_name} yang dilaksanakan pada {exam_date}. Silakan periksa hasil pemeriksaan Anda.',
            'footer_template' => 'Terima kasih, {company_name}. Untuk informasi lebih lanjut, hubungi kami.',
            'is_active' => fake()->boolean(80),
        ];
    }
}
