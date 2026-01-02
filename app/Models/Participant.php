<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Participant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'rank',
        'nrp_nip',
        'position',
        'unit',
        'phone_raw',
        'phone_e164',
        'birth_date',
        'gender',
        'lab_number',
        'exam_date',
        'package_id',
        'package_code',
        'company_id',
        'company_code',
        'wa_opt_in_at',
        'wa_opt_in_source',
        'wa_status',
        'wa_last_error',
        'has_attachment',
        'uploaded_by',
        'pdf_path',
        'pdf_status',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'exam_date' => 'date',
            'wa_opt_in_at' => 'datetime',
            'wa_sent_at' => 'datetime',
            'has_attachment' => 'boolean',
        ];
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function participantAttachments(): HasMany
    {
        return $this->hasMany(ParticipantAttachment::class);
    }

    public function waMessages(): HasMany
    {
        return $this->hasMany(WaMessage::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('uploaded_by', $userId);
    }

    public function hasPdf(): bool
    {
        return $this->pdf_status === 'uploaded' && !empty($this->pdf_path);
    }
}
