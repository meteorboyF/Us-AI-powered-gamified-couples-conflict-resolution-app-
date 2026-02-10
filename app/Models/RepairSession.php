<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RepairSession extends Model
{
    protected $fillable = [
        'couple_id',
        'initiated_by',
        'status',
        'conflict_topic',
        'initiator_perspective',
        'partner_perspective',
        'shared_goals',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'shared_goals' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the couple this session belongs to
     */
    public function couple()
    {
        return $this->belongsTo(Couple::class);
    }

    /**
     * Get the user who initiated the session
     */
    public function initiator()
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    /**
     * Get the agreements created in this session
     */
    public function agreements()
    {
        return $this->hasMany(RepairAgreement::class);
    }

    /**
     * Start the session (mark as in_progress)
     */
    public function start(): void
    {
        $this->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);
    }

    /**
     * Complete the session
     */
    public function complete(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    /**
     * Abandon the session
     */
    public function abandon(): void
    {
        $this->update(['status' => 'abandoned']);
    }

    /**
     * Check if session is waiting for partner
     */
    public function isWaitingForPartner(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if user can join this session
     */
    public function canBeJoined(User $user): bool
    {
        // Must be pending and user must be the partner (not initiator)
        return $this->status === 'pending' && $this->initiated_by !== $user->id;
    }

    /**
     * Get the partner user (not the initiator)
     */
    public function getPartner(): ?User
    {
        return $this->couple->users()
            ->where('users.id', '!=', $this->initiated_by)
            ->first();
    }

    /**
     * Scope to get active sessions
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'in_progress']);
    }

    /**
     * Scope to get completed sessions
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope to filter by couple
     */
    public function scopeForCouple($query, Couple $couple)
    {
        return $query->where('couple_id', $couple->id);
    }
}
