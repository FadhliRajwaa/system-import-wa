<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Replace SaungWA columns with Wablas columns in users table
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Rename saungwa_appkey to wablas_token
            $table->renameColumn('saungwa_appkey', 'wablas_token');

            // Rename saungwa_phone to wablas_phone
            $table->renameColumn('saungwa_phone', 'wablas_phone');

            // Drop saungwa_authkey (not needed for Wablas - uses single token)
            $table->dropColumn('saungwa_authkey');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Reverse: rename back to saungwa columns
            $table->renameColumn('wablas_token', 'saungwa_appkey');
            $table->renameColumn('wablas_phone', 'saungwa_phone');

            // Re-add saungwa_authkey
            $table->string('saungwa_authkey', 500)->nullable()->after('saungwa_appkey');
        });
    }
};
