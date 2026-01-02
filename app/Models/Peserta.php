<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Peserta extends Model
{
    protected $table = 'peserta';
    
    // COMPOSITE PRIMARY KEY: nrp_nip + tanggal_periksa
    // Laravel tidak sepenuhnya mendukung composite PK, jadi kita set primary key ke salah satu
    // dan handle logic di level aplikasi
    protected $primaryKey = 'nrp_nip';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'nrp_nip',
        'nama',
        'pangkat',
        'jabatan',
        'satuan_kerja',
        'no_hp_raw',
        'no_hp_wa',
        'tanggal_lahir',
        'jenis_kelamin',
        'no_lab',
        'tanggal_periksa',
        'kode_paket',
        'kode_instansi',
        'status_pdf',
        'path_pdf',
        'status_wa',
        'waktu_kirim_wa',
        'error_wa',
        'diupload_oleh',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'tanggal_periksa' => 'date',
        'waktu_kirim_wa' => 'datetime',
    ];

    /**
     * Override getKey untuk composite primary key
     * Mengembalikan kombinasi nrp_nip + tanggal_periksa
     */
    public function getKey()
    {
        $tanggalPeriksa = $this->tanggal_periksa instanceof \Carbon\Carbon 
            ? $this->tanggal_periksa->format('Y-m-d') 
            : $this->tanggal_periksa;
        return $this->nrp_nip . '|' . $tanggalPeriksa;
    }

    /**
     * CRITICAL: Override setKeysForSaveQuery untuk COMPOSITE PRIMARY KEY
     * Ini memastikan UPDATE dan DELETE hanya mempengaruhi baris dengan 
     * kombinasi nrp_nip + tanggal_periksa yang tepat
     */
    protected function setKeysForSaveQuery($query)
    {
        $nrpNip = $this->getAttribute('nrp_nip');
        $tanggalPeriksa = $this->getAttribute('tanggal_periksa');
        
        // Format tanggal ke Y-m-d string untuk exact match
        $tanggalFormatted = $tanggalPeriksa instanceof \Carbon\Carbon 
            ? $tanggalPeriksa->format('Y-m-d') 
            : $tanggalPeriksa;
        
        // Gunakan getOriginal untuk mendapatkan nilai asli sebelum perubahan
        // Ini penting saat update karena tanggal_periksa bisa berubah
        $originalNrpNip = $this->getOriginal('nrp_nip') ?? $nrpNip;
        $originalTanggal = $this->getOriginal('tanggal_periksa');
        $originalTanggalFormatted = $originalTanggal instanceof \Carbon\Carbon 
            ? $originalTanggal->format('Y-m-d') 
            : ($originalTanggal ?? $tanggalFormatted);
        
        $query->where('nrp_nip', $originalNrpNip);
        $query->where('tanggal_periksa', $originalTanggalFormatted);
        
        return $query;
    }

    /**
     * CRITICAL: Override getKeyForSaveQuery untuk COMPOSITE PRIMARY KEY
     * Memastikan Eloquent menggunakan value yang benar saat save
     */
    protected function getKeyForSaveQuery()
    {
        return $this->getKey();
    }

    /**
     * Relasi ke user yang mengupload
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diupload_oleh');
    }

    /**
     * Relasi ke paket
     */
    public function paket(): BelongsTo
    {
        return $this->belongsTo(Paket::class, 'kode_paket', 'kode');
    }

    /**
     * Relasi ke instansi
     */
    public function instansi(): BelongsTo
    {
        return $this->belongsTo(Instansi::class, 'kode_instansi', 'kode');
    }

    /**
     * Relasi ke pesan WA
     */
    public function pesanWa(): HasMany
    {
        return $this->hasMany(PesanWa::class, 'nrp_nip_peserta', 'nrp_nip');
    }

    /**
     * Scope untuk filter berdasarkan user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('diupload_oleh', $userId);
    }

    /**
     * Cek apakah sudah ada PDF
     */
    public function sudahAdaPdf(): bool
    {
        return $this->status_pdf === 'uploaded' && !empty($this->path_pdf);
    }

    /**
     * Cek apakah sudah dikirim WA
     */
    public function sudahDikirimWa(): bool
    {
        return $this->status_wa === 'sent';
    }

    /**
     * Static method untuk find by composite key
     */
    public static function findByCompositeKey(string $nrpNip, $tanggalPeriksa)
    {
        $tanggal = $tanggalPeriksa instanceof \Carbon\Carbon 
            ? $tanggalPeriksa->format('Y-m-d') 
            : $tanggalPeriksa;
        
        // Gunakan where biasa, bukan whereDate untuk exact match
        return static::where('nrp_nip', $nrpNip)
            ->where('tanggal_periksa', $tanggal)
            ->first();
    }
}
