<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ParticipantAttachment>
 */
class ParticipantAttachmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'original_name' => fake()->word().'.pdf',
            'stored_path' => 'attachments/'.fake()->uuid().'.pdf',
            'mime' => 'application/pdf',
            'size' => fake()->numberBetween(1000, 10000000),
            'participant_id' => \App\Models\Participant::factory(),
            'upload_id' => \App\Models\Upload::factory(),
        ];
    }
}
