<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class World extends Model
{
    protected $fillable = [
        'couple_id',
        'theme_type',
        'level',
        'xp_total',
        'ambience_state',
        'cosmetics',
    ];

    protected $casts = [
        'cosmetics' => 'array',
        'level' => 'integer',
        'xp_total' => 'integer',
    ];

    // Relationships
    public function couple()
    {
        return $this->belongsTo(Couple::class);
    }

    // Helper methods
    public function addXp(int $amount): void
    {
        $this->xp_total += $amount;
        $this->updateLevel();
        $this->save();
    }

    protected function updateLevel(): void
    {
        // Simple leveling: every 100 XP = 1 level
        $this->level = floor($this->xp_total / 100) + 1;
    }
}
