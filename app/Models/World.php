<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class World extends Model
{
    protected const AMBIENCE_THRESHOLDS = [
        500 => 'quiet',
        200 => 'calm',
        0 => 'bright',
    ];

    protected const COSMETIC_UNLOCKS = [
        3 => 'blooming_garden',
        5 => 'starlit_sky',
    ];

    protected $fillable = [
        'couple_id',
        'theme_type',
        'world_type',
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

    public function resolvedWorldType(): string
    {
        return $this->world_type ?: ($this->theme_type ?: 'garden');
    }

    // Helper methods
    public function addXp(int $amount): void
    {
        $this->xp_total += $amount;
        $this->updateLevel();
        $this->updateAmbienceState();
        $this->unlockCosmeticsForLevel();
        $this->save();
    }

    public function spendXp(int $amount): void
    {
        if ($amount <= 0) {
            return;
        }

        if ($this->xp_total < $amount) {
            throw new \InvalidArgumentException('Insufficient XP for this upgrade.');
        }

        $this->xp_total -= $amount;
        $this->updateLevel();
        $this->updateAmbienceState();
        $this->save();
    }

    protected function updateLevel(): void
    {
        // Simple leveling: every 100 XP = 1 level
        $this->level = floor($this->xp_total / 100) + 1;
    }

    protected function updateAmbienceState(): void
    {
        foreach (self::AMBIENCE_THRESHOLDS as $xpThreshold => $state) {
            if ($this->xp_total >= $xpThreshold) {
                $this->ambience_state = $state;

                return;
            }
        }
    }

    protected function unlockCosmeticsForLevel(): void
    {
        $cosmetics = $this->cosmetics ?? [];

        foreach (self::COSMETIC_UNLOCKS as $requiredLevel => $cosmetic) {
            if ($this->level >= $requiredLevel && ! in_array($cosmetic, $cosmetics, true)) {
                $cosmetics[] = $cosmetic;
            }
        }

        $this->cosmetics = $cosmetics;
    }
}
