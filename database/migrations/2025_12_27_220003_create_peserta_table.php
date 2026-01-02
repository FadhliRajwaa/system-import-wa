<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Table: peserta (sebelumnya participants)
     * 
     * UNIQUE CONSTRAINT: nrp_nip + tanggal_periksa
     * - NRP/NIP sama + tanggal sama = GAGAL/SKIP
     * - NRP/NIP sama + tanggal beda = SUKSES (data baru)
     */
    public function up(): void
    {
        Schema::create('peserta', function (Blueprint $table) {
            $table->id();
            
            // Data Pribadi
            $table->string('nama', 255);
            $table->string('pangkat', 100)->nullable();
            $table->string('nrp_nip', 50)->nullable();
            $table->string('jabatan', 255)->nullable();
            $table->string('satuan_kerja', 255)->nullable();
            $table->string('no_hp_raw', 50)->nullable();
            $table->string('no_hp_wa', 20)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('jenis_kelamin', 20)->nullable();
            
            // Data Pemeriksaan
            $table->string('no_lab', 50)->nullable();
            $table->date('tanggal_periksa');
            $table->string('kode_paket', 50)->nullable();
            $table->string('kode_instansi', 50)->nullable();
            
            // Status PDF
            $table->enum('status_pdf', ['not_uploaded', 'uploaded'])->default('not_uploaded');
            $table->string('path_pdf', 500)->nullable();
            
            // Status WhatsApp
            $table->enum('status_wa', ['not_sent', 'queued', 'sent', 'failed'])->default('not_sent');
            $table->timestamp('waktu_kirim_wa')->nullable();
            $table->text('error_wa')->nullable();
            
            // Tracking
            $table->foreignId('diupload_oleh')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            // UNIQUE CONSTRAINT: nrp_nip + tanggal_periksa
            $table->unique(['nrp_nip', 'tanggal_periksa'], 'peserta_nrp_nip_tanggal_periksa_unique');
            
            // Indexes
            $table->index('nrp_nip');
            $table->index('tanggal_periksa');
            $table->index('kode_paket');
            $table->index('kode_instansi');
            $table->index('status_wa');
            $table->index('status_pdf');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peserta');
    }
};
