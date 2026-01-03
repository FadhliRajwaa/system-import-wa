<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add 'gagal' status to ENUM for failed messages
        DB::statement("ALTER TABLE pesan_wa MODIFY COLUMN status ENUM('belum_kirim', 'success', 'gagal') DEFAULT 'belum_kirim'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'gagal' from ENUM
        DB::statement("UPDATE pesan_wa SET status = 'belum_kirim' WHERE status = 'gagal'");
        DB::statement("ALTER TABLE pesan_wa MODIFY COLUMN status ENUM('belum_kirim', 'success') DEFAULT 'belum_kirim'");
    }
};
