<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MissionCompletion extends Model
{
    protected $fillable = [
        'mission_assignment_id',
        'user_id',
        'completed_at',
        'partner_acknowledged_at',
        'notes',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'partner_acknowledged_at' => 'datetime',
    ];

    /**
     * Get the mission assignment
     */
    public function assignment()
    {
        return $this->belongsTo(MissionAssignment::class, 'mission_assignment_id');
    }

    /**
     * Get the user who completed the mission
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if partner has acknowledged
     */
    public function isAcknowledged(): bool
    {
        return $this->partner_acknowledged_at !== null;
    }

    /**
     * Mark as acknowledged by partner
     */
    public function acknowledge(): void
    {
        $this->update(['partner_acknowledged_at' => now()]);
    }
}
