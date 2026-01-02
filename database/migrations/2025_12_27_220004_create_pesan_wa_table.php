<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Table: pesan_wa (sebelumnya wa_messages)
     */
    public function up(): void
    {
        Schema::create('pesan_wa', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 50)->default('meta_cloud_api');
            $table->string('no_tujuan', 20);
            $table->text('isi_pesan');
            $table->enum('status', ['queued', 'sent', 'delivered', 'read', 'failed'])->default('queued');
            $table->unsignedInteger('percobaan')->default(0);
            $table->text('error_terakhir')->nullable();
            $table->timestamp('waktu_kirim')->nullable();
            $table->foreignId('id_peserta')->nullable()->constrained('peserta')->onDelete('cascade');
            $table->foreignId('dibuat_oleh')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            // Indexes
            $table->index('status');
            $table->index('no_tujuan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pesan_wa');
    }
};
