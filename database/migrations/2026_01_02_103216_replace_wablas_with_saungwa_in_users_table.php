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
        Schema::table('users', function (Blueprint $table) {
            // Rename wablas_token to saungwa_appkey
            $table->renameColumn('wablas_token', 'saungwa_appkey');

            // Rename wablas_phone to saungwa_phone
            $table->renameColumn('wablas_phone', 'saungwa_phone');

            // Drop wablas_server (not needed for SaungWA)
            $table->dropColumn('wablas_server');

            // Add saungwa_authkey column
            $table->string('saungwa_authkey', 500)->nullable()->after('saungwa_appkey');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Reverse: rename back to wablas columns
            $table->renameColumn('saungwa_appkey', 'wablas_token');
            $table->renameColumn('saungwa_phone', 'wablas_phone');

            // Drop saungwa_authkey
            $table->dropColumn('saungwa_authkey');

            // Re-add wablas_server
            $table->string('wablas_server', 50)->default('solo')->after('wablas_token');
        });
    }
};
