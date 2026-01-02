<?php

namespace Database\Seeders;

use App\Models\WaMessage;
use Illuminate\Database\Seeder;

class WaMessageSeeder extends Seeder
{
    public function run(): void
    {
        WaMessage::factory()->count(30)->create();
    }
}
