<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VaultItem extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'couple_id',
        'created_by_user_id',
        'type',
        'title',
        'body',
        'media_disk',
        'media_path',
        'media_mime',
        'media_size',
        'sha256',
        'is_sensitive',
        'is_locked',
        'locked_pin_hash',
        'meta',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_sensitive' => 'boolean',
            'is_locked' => 'boolean',
            'meta' => 'array',
        ];
    }

    public function couple(): BelongsTo
    {
        return $this->belongsTo(Couple::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function unlockRequests(): HasMany
    {
        return $this->hasMany(VaultUnlockRequest::class);
    }
}
