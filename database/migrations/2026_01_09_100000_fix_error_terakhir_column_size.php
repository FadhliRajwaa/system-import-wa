<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Fix: Ensure error_terakhir column is TEXT type to store full error messages
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE pesan_wa MODIFY COLUMN error_terakhir TEXT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE pesan_wa MODIFY COLUMN error_terakhir VARCHAR(255) NULL");
    }
};
