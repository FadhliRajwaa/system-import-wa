<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Upload>
 */
class UploadFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement(['uploaded', 'parsed', 'imported', 'failed']);
        $totalRows = fake()->numberBetween(10, 100);

        return [
            'type' => fake()->randomElement(['data_excel', 'attachment']),
            'original_name' => fake()->word().'.xlsx',
            'stored_path' => 'uploads/'.fake()->uuid().'.xlsx',
            'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'size' => fake()->numberBetween(1000, 500000),
            'status' => $status,
            'uploaded_by' => \App\Models\User::factory(),
            'total_rows' => $totalRows,
            'success_rows' => match ($status) {
                'uploaded' => 0,
                'parsed' => 0,
                'imported' => $totalRows,
                'failed' => 0,
            },
            'failed_rows' => match ($status) {
                'uploaded' => 0,
                'parsed' => 0,
                'imported' => 0,
                'failed' => $totalRows,
            },
        ];
    }
}
