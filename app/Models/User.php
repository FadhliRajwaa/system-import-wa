<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
        'role',
        'kode_user',
        'saungwa_appkey',
        'saungwa_authkey',
        'saungwa_phone',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    public function scopeAdmins(Builder $query): Builder
    {
        return $query->where('role', 'admin');
    }

    public function scopeUsers(Builder $query): Builder
    {
        return $query->where('role', 'user');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('is_active', false);
    }

    public function toggleActive(): bool
    {
        $this->is_active = ! $this->is_active;
        $this->save();

        return $this->is_active;
    }

    public function deactivate(): void
    {
        $this->is_active = false;
        $this->save();
    }

    public function activate(): void
    {
        $this->is_active = true;
        $this->save();
    }

    public function canDelete(?User $currentUser = null): bool
    {
        $currentUser = $currentUser ?? auth()->user();

        if ($currentUser && $this->id === $currentUser->id) {
            return false;
        }

        $activeAdminsCount = static::active()->count();

        if ($activeAdminsCount <= 1 && $this->is_active) {
            return false;
        }

        return true;
    }

    public function getInitialsAttribute(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    public function initials(): string
    {
        return $this->initials;
    }

    public function getAvatarUrlAttribute(): string
    {
        return sprintf(
            'https://ui-avatars.com/api/?name=%s&background=random&color=fff',
            urlencode($this->name)
        );
    }

    public function uploads(): HasMany
    {
        return $this->hasMany(Upload::class, 'uploaded_by');
    }

    public function waMessages(): HasMany
    {
        return $this->hasMany(WaMessage::class, 'created_by');
    }

    public function uploadedParticipants(): HasMany
    {
        return $this->hasMany(Participant::class, 'uploaded_by');
    }
}
