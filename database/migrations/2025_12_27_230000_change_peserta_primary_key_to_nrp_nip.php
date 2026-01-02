<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * PERUBAHAN BESAR:
     * - nrp_nip menjadi PRIMARY KEY (tidak boleh null)
     * - Unique constraint: nrp_nip + tanggal_periksa
     * - id dihapus (nomor urut akan di-generate dinamis di aplikasi)
     */
    public function up(): void
    {
        // Drop table lama
        Schema::dropIfExists('pesan_wa');
        Schema::dropIfExists('peserta');

        // Buat table peserta dengan nrp_nip sebagai primary key
        Schema::create('peserta', function (Blueprint $table) {
            // PRIMARY KEY adalah nrp_nip (VARCHAR)
            $table->string('nrp_nip', 50)->primary();
            
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
            // Ini memungkinkan peserta dengan NRP/NIP sama tapi tanggal periksa berbeda
            $table->unique(['nrp_nip', 'tanggal_periksa'], 'peserta_nrp_tanggal_unique');
            
            // Indexes
            $table->index('tanggal_periksa');
            $table->index('kode_paket');
            $table->index('kode_instansi');
            $table->index('status_wa');
            $table->index('status_pdf');
        });

        // Buat table pesan_wa dengan foreign key ke nrp_nip
        Schema::create('pesan_wa', function (Blueprint $table) {
            $table->id(); // Auto-increment untuk pesan
            $table->string('provider', 50)->default('meta_cloud_api');
            $table->string('no_tujuan', 20);
            $table->text('isi_pesan');
            $table->enum('status', ['queued', 'sent', 'delivered', 'read', 'failed'])->default('queued');
            $table->unsignedInteger('percobaan')->default(0);
            $table->text('error_terakhir')->nullable();
            $table->timestamp('waktu_kirim')->nullable();
            
            // Foreign key ke peserta (nrp_nip)
            $table->string('nrp_nip_peserta', 50)->nullable();
            $table->foreign('nrp_nip_peserta')->references('nrp_nip')->on('peserta')->onDelete('cascade');
            
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
