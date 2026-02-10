<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mission extends Model
{
    protected $fillable = [
        'title',
        'description',
        'type',
        'xp_reward',
        'category',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'xp_reward' => 'integer',
    ];

    /**
     * Get the mission assignments for this mission
     */
    public function assignments()
    {
        return $this->hasMany(MissionAssignment::class);
    }

    /**
     * Scope to get only active missions
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get missions by type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
