<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * PERBAIKAN: 
     * - COMPOSITE PRIMARY KEY: nrp_nip + tanggal_periksa
     * - Ini memungkinkan peserta dengan NRP/NIP sama tapi tanggal periksa berbeda
     */
    public function up(): void
    {
        // Drop table lama
        Schema::dropIfExists('pesan_wa');
        Schema::dropIfExists('peserta');

        // Buat table peserta dengan COMPOSITE PRIMARY KEY
        Schema::create('peserta', function (Blueprint $table) {
            // Data identitas
            $table->string('nrp_nip', 50);
            $table->date('tanggal_periksa');
            
            // COMPOSITE PRIMARY KEY: nrp_nip + tanggal_periksa
            // Ini memungkinkan:
            // - 73070427 + 20/01/2025 = OK
            // - 73070427 + 25/01/2025 = OK (tanggal beda)
            // - 73070427 + 20/01/2025 = GAGAL (duplikat)
            $table->primary(['nrp_nip', 'tanggal_periksa']);
            
            // Data Pribadi
            $table->string('nama', 255);
            $table->string('pangkat', 100)->nullable();
            $table->string('jabatan', 255)->nullable();
            $table->string('satuan_kerja', 255)->nullable();
            $table->string('no_hp_raw', 50)->nullable();
            $table->string('no_hp_wa', 20)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('jenis_kelamin', 20)->nullable();
            
            // Data Pemeriksaan
            $table->string('no_lab', 50)->nullable();
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
            
            // Indexes
            $table->index('nrp_nip');
            $table->index('tanggal_periksa');
            $table->index('kode_paket');
            $table->index('kode_instansi');
            $table->index('status_wa');
            $table->index('status_pdf');
        });

        // Buat table pesan_wa
        Schema::create('pesan_wa', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 50)->default('meta_cloud_api');
            $table->string('no_tujuan', 20);
            $table->text('isi_pesan');
            $table->enum('status', ['queued', 'sent', 'delivered', 'read', 'failed'])->default('queued');
            $table->unsignedInteger('percobaan')->default(0);
            $table->text('error_terakhir')->nullable();
            $table->timestamp('waktu_kirim')->nullable();
            
            // Foreign key ke peserta (composite)
            $table->string('nrp_nip_peserta', 50)->nullable();
            $table->date('tanggal_periksa_peserta')->nullable();
            $table->foreign(['nrp_nip_peserta', 'tanggal_periksa_peserta'])
                  ->references(['nrp_nip', 'tanggal_periksa'])
                  ->on('peserta')
                  ->onDelete('cascade');
            
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
        Schema::dropIfExists('peserta');
    }
};
