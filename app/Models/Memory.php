<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Memory extends Model
{
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
        'locked_at',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
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
        return $this->visibility === 'locked';
    }

    /**
     * Lock this memory (make it special)
     */
    public function lock(): void
    {
        $this->update([
            'visibility' => 'locked',
            'locked_at' => now(),
        ]);
    }

    /**
     * Unlock this memory
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
        // Creator can always view
        if ($this->created_by === $user->id) {
            return true;
        }

        // Private memories only visible to creator
        if ($this->isPrivate()) {
            return false;
        }

        // Shared and locked memories visible to both partners
        return $this->couple->users()->where('users.id', $user->id)->exists();
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
        if (!$this->file_path) {
            return null;
        }

        return Storage::url($this->file_path);
    }

    /**
     * Get thumbnail URL for videos
     */
    public function getThumbnailUrl(): ?string
    {
        if (!$this->thumbnail_path) {
            return null;
        }

        return Storage::url($this->thumbnail_path);
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
                // Or shared/locked memories in user's couple
                ->orWhere(function ($q2) use ($user) {
                    $q2->whereIn('visibility', ['shared', 'locked'])
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
        return $query->where('visibility', 'locked');
    }

    /**
     * Scope to order by most recent
     */
    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}
