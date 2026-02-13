<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'couple_id',
        'user_id',
        'content',
        'type',
        'metadata',
        'read_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'read_at' => 'datetime',
    ];

    /**
     * Get the couple this message belongs to
     */
    public function couple()
    {
        return $this->belongsTo(Couple::class);
    }

    /**
     * Get the user who sent this message
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mark message as read
     */
    public function markAsRead(): void
    {
        if (! $this->read_at) {
            $this->update(['read_at' => now()]);
        }
    }

    /**
     * Check if message is a love button
     */
    public function isLoveButton(): bool
    {
        return $this->type === 'love_button';
    }

    /**
     * Check if message is unread
     */
    public function isUnread(): bool
    {
        return $this->read_at === null;
    }

    /**
     * Scope to get unread messages
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope to filter by couple
     */
    public function scopeForCouple($query, Couple $couple)
    {
        return $query->where('couple_id', $couple->id);
    }

    /**
     * Scope to get messages for a specific user (not sent by them)
     */
    public function scopeForUser($query, User $user)
    {
        return $query->where('user_id', '!=', $user->id);
    }
}
