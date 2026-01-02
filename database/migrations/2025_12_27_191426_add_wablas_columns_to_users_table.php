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
            $table->string('wablas_token', 255)->nullable()->after('role');
            $table->string('wablas_server', 50)->default('solo')->after('wablas_token');
            $table->string('wablas_phone', 20)->nullable()->after('wablas_server');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['wablas_token', 'wablas_server', 'wablas_phone']);
        });
    }
};
