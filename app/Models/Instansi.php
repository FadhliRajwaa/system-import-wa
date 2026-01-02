<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Instansi extends Model
{
    protected $table = 'instansi';

    protected $fillable = [
        'kode',
        'nama',
        'template_prolog',
        'template_footer',
    ];

    /**
     * Relasi ke peserta
     */
    public function peserta(): HasMany
    {
        return $this->hasMany(Peserta::class, 'kode_instansi', 'kode');
    }
}

