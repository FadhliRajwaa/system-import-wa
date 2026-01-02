<?php

namespace Database\Seeders;

use App\Models\ParticipantAttachment;
use Illuminate\Database\Seeder;

class ParticipantAttachmentSeeder extends Seeder
{
    public function run(): void
    {
        ParticipantAttachment::factory()->count(30)->create();
    }
}
