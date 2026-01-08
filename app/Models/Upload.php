<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Upload extends Model
{
    use HasFactory;

    protected $table = 'unggahan';

    protected $fillable = [
        'type',
        'original_name',
        'stored_path',
        'mime',
        'size',
        'status',
        'total_rows',
        'success_rows',
        'failed_rows',
        'error_summary',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'type' => 'string',
            'status' => 'string',
            'error_summary' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function participantAttachments(): HasMany
    {
        return $this->hasMany(ParticipantAttachment::class);
    }
}
