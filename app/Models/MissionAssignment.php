<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MissionAssignment extends Model
{
    protected $fillable = [
        'couple_id',
        'mission_id',
        'assigned_for_date',
        'status',
    ];

    protected $casts = [
        'assigned_for_date' => 'date',
    ];

    /**
     * Get the couple this assignment belongs to
     */
    public function couple()
    {
        return $this->belongsTo(Couple::class);
    }

    /**
     * Get the mission template
     */
    public function mission()
    {
        return $this->belongsTo(Mission::class);
    }

    /**
     * Get the completions for this assignment
     */
    public function completions()
    {
        return $this->hasMany(MissionCompletion::class);
    }

    /**
     * Check if the mission is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Mark as completed
     */
    public function markCompleted(): void
    {
        $this->update(['status' => 'completed']);
    }

    /**
     * Scope to get pending assignments
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get assignments for today
     */
    public function scopeForToday($query)
    {
        return $query->whereDate('assigned_for_date', today());
    }
}
