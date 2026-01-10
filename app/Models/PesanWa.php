<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PesanWa extends Model
{
    protected $table = 'pesan_wa';

    protected $fillable = [
        'provider',
        'no_tujuan',
        'isi_pesan',
        'status',
        'percobaan',
        'error_terakhir',
        'waktu_kirim',
        'nrp_nip_peserta',
        'tanggal_periksa_peserta',
        'dibuat_oleh',
    ];

    protected $casts = [
        'waktu_kirim' => 'datetime',
        'percobaan' => 'integer',
    ];

    /**
     * Relasi ke peserta (via nrp_nip)
     */
    public function peserta(): BelongsTo
    {
        return $this->belongsTo(Peserta::class, 'nrp_nip_peserta', 'nrp_nip');
    }

    /**
     * Relasi ke user yang membuat
     */
    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    /**
     * Scope untuk filter berdasarkan status
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope untuk yang belum terkirim
     */
    public function scopeBelumTerkirim($query)
    {
        return $query->where('status', 'belum_kirim');
    }

    /**
     * Scope untuk yang sudah terkirim (success)
     */
    public function scopeTerkirim($query)
    {
        return $query->where('status', 'success');
    }
}
