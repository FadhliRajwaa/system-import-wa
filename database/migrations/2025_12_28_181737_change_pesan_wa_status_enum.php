<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Add new enum values first (expand enum)
        DB::statement("ALTER TABLE pesan_wa MODIFY COLUMN status ENUM('queued', 'sent', 'delivered', 'read', 'failed', 'belum_kirim', 'success') DEFAULT 'queued'");
        
        // Step 2: Convert existing data to new status values
        DB::statement("UPDATE pesan_wa SET status = 'belum_kirim' WHERE status IN ('queued', 'failed')");
        DB::statement("UPDATE pesan_wa SET status = 'success' WHERE status IN ('sent', 'delivered', 'read')");
        
        // Step 3: Remove old enum values (shrink enum)
        DB::statement("ALTER TABLE pesan_wa MODIFY COLUMN status ENUM('belum_kirim', 'success') DEFAULT 'belum_kirim'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum values
        DB::statement("ALTER TABLE pesan_wa MODIFY COLUMN status ENUM('queued', 'sent', 'delivered', 'read', 'failed') DEFAULT 'queued'");
        
        // Convert data back
        DB::statement("UPDATE pesan_wa SET status = 'queued' WHERE status = 'belum_kirim'");
        DB::statement("UPDATE pesan_wa SET status = 'sent' WHERE status = 'success'");
    }
};
