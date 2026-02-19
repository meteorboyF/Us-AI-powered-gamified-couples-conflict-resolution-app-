<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

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
        ];
    }

    public function couples(): BelongsToMany
    {
        return $this->belongsToMany(Couple::class, 'couple_members')
            ->withPivot(['role', 'joined_at'])
            ->withTimestamps();
    }

    public function currentCouple(): BelongsTo
    {
        return $this->belongsTo(Couple::class, 'current_couple_id');
    }

    public function vaultItems(): HasMany
    {
        return $this->hasMany(VaultItem::class, 'created_by_user_id');
    }

    public function aiSessions(): HasMany
    {
        return $this->hasMany(AiSession::class, 'created_by_user_id');
    }

    public function aiMessages(): HasMany
    {
        return $this->hasMany(AiMessage::class, 'sender_user_id');
    }

    public function aiDrafts(): HasMany
    {
        return $this->hasMany(AiDraft::class, 'created_by_user_id');
    }
}
