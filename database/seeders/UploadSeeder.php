<?php

namespace Database\Seeders;

use App\Models\Upload;
use Illuminate\Database\Seeder;

class UploadSeeder extends Seeder
{
    public function run(): void
    {
        Upload::factory()->count(20)->create();
    }
}
