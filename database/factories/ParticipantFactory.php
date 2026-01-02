<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Participant>
 */
class ParticipantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $phoneRaw = '08'.fake()->numerify('##########');

        return [
            'name' => fake()->name(),
            'rank' => fake()->randomElement(['Pemula', 'Muda', 'Madya', 'Utama', 'Ahli Utama']),
            'nrp_nip' => fake()->numerify('############'),
            'position' => fake()->randomElement(['Staff', 'Supervisor', 'Manager', 'Direktur']),
            'unit' => fake()->randomElement(['HRD', 'Finance', 'IT', 'Operasional', 'Marketing']),
            'phone_raw' => $phoneRaw,
            'phone_e164' => '62'.substr($phoneRaw, 1),
            'birth_date' => fake()->dateTimeBetween('-60 years', '-20 years')->format('Y-m-d'),
            'gender' => fake()->randomElement(['Pria', 'Wanita']),
            'lab_number' => 'LAB-'.fake()->numerify('######'),
            'exam_date' => fake()->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
            'package_id' => \App\Models\Package::factory(),
            'package_code' => fn (array $attributes) => \App\Models\Package::find($attributes['package_id'])?->code,
            'company_id' => \App\Models\Company::factory(),
            'company_code' => fn (array $attributes) => \App\Models\Company::find($attributes['company_id'])?->code,
            'wa_opt_in_at' => fake()->boolean(70) ? fake()->dateTimeBetween('-1 month', 'now') : null,
            'wa_opt_in_source' => fake()->randomElement(['web_form', 'sms', 'email', 'manual']),
            'wa_status' => fake()->randomElement(['not_sent', 'not_sent', 'not_sent', 'not_sent', 'not_sent', 'queued', 'sent', 'failed']),
            'wa_sent_at' => fn (array $attributes) => in_array($attributes['wa_status'], ['sent', 'failed']) ? fake()->dateTimeBetween('-2 weeks', 'now') : null,
            'wa_last_error' => fn (array $attributes) => $attributes['wa_status'] === 'failed' ? fake()->sentence() : null,
            'has_attachment' => fake()->boolean(40),
        ];
    }
}
