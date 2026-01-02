<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('paket', function (Blueprint $table) {
            // Drop foreign key first if exists
            $table->dropForeign(['dibuat_oleh']);
            
            // Drop columns yang tidak diperlukan
            $table->dropColumn(['deskripsi', 'aktif', 'dibuat_oleh']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('paket', function (Blueprint $table) {
            $table->text('deskripsi')->nullable()->after('nama');
            $table->boolean('aktif')->default(true)->after('deskripsi');
            $table->foreignId('dibuat_oleh')->nullable()->after('aktif')->constrained('users')->nullOnDelete();
        });
    }
};
