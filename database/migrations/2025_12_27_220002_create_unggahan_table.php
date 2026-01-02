<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Table: unggahan (sebelumnya uploads)
     */
    public function up(): void
    {
        Schema::create('unggahan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('diupload_oleh')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('tipe', ['data_excel', 'lampiran'])->default('data_excel');
            $table->string('nama_file_asli', 255);
            $table->string('path_tersimpan', 500);
            $table->string('mime', 100)->nullable();
            $table->unsignedBigInteger('ukuran')->default(0);
            $table->enum('status', ['uploaded', 'parsed', 'imported', 'failed'])->default('uploaded');
            $table->unsignedInteger('total_baris')->default(0);
            $table->unsignedInteger('baris_sukses')->default(0);
            $table->unsignedInteger('baris_gagal')->default(0);
            $table->json('ringkasan_error')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unggahan');
    }
};
