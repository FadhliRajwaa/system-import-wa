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
        // Add dibuat_oleh to paket table
        Schema::table('paket', function (Blueprint $table) {
            $table->foreignId('dibuat_oleh')
                ->nullable()
                ->after('aktif')
                ->constrained('users')
                ->nullOnDelete();
        });

        // Add dibuat_oleh to instansi table
        Schema::table('instansi', function (Blueprint $table) {
            $table->foreignId('dibuat_oleh')
                ->nullable()
                ->after('aktif')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('paket', function (Blueprint $table) {
            $table->dropForeign(['dibuat_oleh']);
            $table->dropColumn('dibuat_oleh');
        });

        Schema::table('instansi', function (Blueprint $table) {
            $table->dropForeign(['dibuat_oleh']);
            $table->dropColumn('dibuat_oleh');
        });
    }
};
