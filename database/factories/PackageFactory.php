<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Package>
 */
class PackageFactory extends Factory
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
            'code' => 'PKT'.str_pad((string) $counter++, 3, '0', STR_PAD_LEFT),
            'name' => fake()->randomElement([
                'Paket Pemeriksaan Kesehatan Dasar',
                'Paket Medical Checkup Komplit',
                'Paket Pemeriksaan Gizi',
                'Paket Pemeriksaan Mata',
                'Paket Pemeriksaan THT',
                'Paket Pemeriksaan Jantung',
                'Paket Pemeriksaan Laboratorium Dasar',
                'Paket Pemeriksaan Kesehatan Wanita',
                'Paket Pemeriksaan Kesehatan Pria',
                'Paket Pemeriksaan Kesehatan Lanjut Usia',
                'Paket Pemeriksaan Narkoba',
            ]),
            'description' => fake()->sentence(),
            'is_active' => fake()->boolean(80),
        ];
    }
}
