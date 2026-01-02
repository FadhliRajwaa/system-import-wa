<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Paket extends Model
{
    protected $table = 'paket';

    protected $fillable = [
        'kode',
        'nama',
    ];

    /**
     * Relasi ke peserta
     */
    public function peserta(): HasMany
    {
        return $this->hasMany(Peserta::class, 'kode_paket', 'kode');
    }
}
