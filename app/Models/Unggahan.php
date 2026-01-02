<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Unggahan extends Model
{
    protected $table = 'unggahan';

    protected $fillable = [
        'diupload_oleh',
        'tipe',
        'nama_file_asli',
        'path_tersimpan',
        'mime',
        'ukuran',
        'status',
        'total_baris',
        'baris_sukses',
        'baris_gagal',
        'ringkasan_error',
    ];

    protected $casts = [
        'ringkasan_error' => 'array',
        'ukuran' => 'integer',
        'total_baris' => 'integer',
        'baris_sukses' => 'integer',
        'baris_gagal' => 'integer',
    ];

    /**
     * Relasi ke user yang mengupload
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diupload_oleh');
    }

    /**
     * Scope untuk filter berdasarkan tipe
     */
    public function scopeTipe($query, string $tipe)
    {
        return $query->where('tipe', $tipe);
    }
}
