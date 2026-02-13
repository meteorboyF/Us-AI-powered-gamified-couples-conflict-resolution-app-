<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Memory extends Model
{
    public const MEDIA_DISK = 'public';

    protected $fillable = [
        'couple_id',
        'created_by',
        'type',
        'title',
        'description',
        'file_path',
        'thumbnail_path',
        'file_size',
        'mime_type',
        'visibility',
        'comfort',
        'locked_at',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'comfort' => 'boolean',
        'locked_at' => 'datetime',
    ];

    /**
     * Get the couple this memory belongs to
     */
    public function couple()
    {
        return $this->belongsTo(Couple::class);
    }

    /**
     * Get the user who created this memory
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the reactions for this memory
     */
    public function reactions()
    {
        return $this->hasMany(MemoryReaction::class);
    }

    public function unlockApprovals(): HasMany
    {
        return $this->hasMany(VaultUnlock::class);
    }

    /**
     * Type checks
     */
    public function isPhoto(): bool
    {
        return $this->type === 'photo';
    }

    public function isVideo(): bool
    {
        return $this->type === 'video';
    }

    public function isVoiceNote(): bool
    {
        return $this->type === 'voice_note';
    }

    public function isText(): bool
    {
        return $this->type === 'text';
    }

    /**
     * Visibility checks
     */
    public function isPrivate(): bool
    {
        return $this->visibility === 'private';
    }

    public function isShared(): bool
    {
        return $this->visibility === 'shared';
    }

    public function isLocked(): bool
    {
        return $this->isDual();
    }

    public function isDual(): bool
    {
        return in_array($this->visibility, ['dual', 'locked'], true);
    }

    /**
     * Lock this memory (make it special)
     */
    public function lock(): void
    {
        $this->update([
            'visibility' => 'dual',
            'locked_at' => now(),
        ]);
    }

    /**
     * Legacy unlock path
     */
    public function unlock(): void
    {
        $this->update([
            'visibility' => 'shared',
            'locked_at' => null,
        ]);
    }

    /**
     * Check if user can view this memory
     */
    public function canBeViewedBy(User $user): bool
    {
        if (! $this->canBeAccessedBy($user)) {
            return false;
        }

        if (! $this->isDual()) {
            return true;
        }

        return $this->hasActiveDualUnlock();
    }

    /**
     * Check if user can access this memory page and metadata
     */
    public function canBeAccessedBy(User $user): bool
    {
        // Creator can always view
        if ($this->created_by === $user->id) {
            return true;
        }

        // Private memories only visible to creator
        if ($this->isPrivate()) {
            return false;
        }

        if (! $this->couple->isActive()) {
            return false;
        }

        // Shared/dual memories visible to active couple members only
        return $this->couple->users()
            ->where('users.id', $user->id)
            ->where('couple_user.is_active', true)
            ->exists();
    }

    public function hasActiveDualUnlock(): bool
    {
        if (! $this->isDual()) {
            return true;
        }

        $now = now();
        $activeMemberIds = $this->couple->users()
            ->where('couple_user.is_active', true)
            ->pluck('users.id')
            ->values();

        if ($activeMemberIds->count() !== 2) {
            return false;
        }

        $validApproverIds = $this->unlockApprovals()
            ->whereIn('user_id', $activeMemberIds)
            ->whereNotNull('approved_at')
            ->where(function ($query) use ($now) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', $now);
            })
            ->pluck('user_id')
            ->unique()
            ->values();

        return $validApproverIds->count() === $activeMemberIds->count();
    }

    /**
     * Check if user can delete this memory
     */
    public function canBeDeletedBy(User $user): bool
    {
        // Locked memories cannot be deleted
        if ($this->isLocked()) {
            return false;
        }

        // Only creator can delete
        return $this->created_by === $user->id;
    }

    /**
     * Get public URL for file
     */
    public function getFileUrl(): ?string
    {
        if (! $this->file_path) {
            return null;
        }

        $disk = Storage::disk(self::MEDIA_DISK);

        if (! $disk->exists($this->file_path)) {
            return null;
        }

        return $disk->url($this->file_path);
    }

    /**
     * Get thumbnail URL for videos
     */
    public function getThumbnailUrl(): ?string
    {
        if (! $this->thumbnail_path) {
            return null;
        }

        $disk = Storage::disk(self::MEDIA_DISK);

        if (! $disk->exists($this->thumbnail_path)) {
            return null;
        }

        return $disk->url($this->thumbnail_path);
    }

    /**
     * Scope to filter by couple
     */
    public function scopeForCouple($query, Couple $couple)
    {
        return $query->where('couple_id', $couple->id);
    }

    /**
     * Scope to filter by type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to get memories visible to user
     */
    public function scopeVisible($query, User $user)
    {
        return $query->where(function ($q) use ($user) {
            // Own memories
            $q->where('created_by', $user->id)
                // Or shared/dual memories in user's couple
                ->orWhere(function ($q2) use ($user) {
                    $q2->whereIn('visibility', ['shared', 'dual', 'locked'])
                        ->whereHas('couple.users', function ($q3) use ($user) {
                            $q3->where('users.id', $user->id);
                        });
                });
        });
    }

    /**
     * Scope to get locked memories
     */
    public function scopeLocked($query)
    {
        return $query->whereIn('visibility', ['dual', 'locked']);
    }

    /**
     * Scope to order by most recent
     */
    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}
