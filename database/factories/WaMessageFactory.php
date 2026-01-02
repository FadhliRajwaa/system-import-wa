<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WaMessage>
 */
class WaMessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $participant = \App\Models\Participant::factory();

        return [
            'participant_id' => $participant,
            'created_by' => \App\Models\User::factory(),
            'provider' => fake()->randomElement(['meta_cloud_api', 'twilio', 'other']),
            'to_phone_e164' => fn (array $attributes) => \App\Models\Participant::find($attributes['participant_id'])?->phone_e164,
            'message_body' => fake()->paragraph(),
            'status' => fake()->randomElement(['queued', 'sent', 'failed']),
            'provider_message_id' => 'msg_'.fake()->uuid(),
            'sent_at' => fn (array $attributes) => $attributes['status'] === 'sent' ? fake()->dateTimeBetween('-1 week', 'now') : null,
            'error_message' => fn (array $attributes) => $attributes['status'] === 'failed' ? fake()->sentence() : null,
            'attempts' => fake()->numberBetween(1, 5),
        ];
    }
}
