<?php

namespace App\Services;

use App\Models\Couple;
use App\Models\MoodCheckin;
use App\Models\RepairSession;
use App\Models\World;
use Carbon\Carbon;

class WorldVibeService
{
    public function refreshForCouple(Couple $couple): ?World
    {
        $world = $couple->world;
        if (! $world) {
            return null;
        }

        $metrics = $this->vibeMetrics($couple);
        $vibeScore = $this->vibeScore($metrics, $world);
        $ambienceState = $this->ambienceFromScore($vibeScore);

        $meta = $this->meta($world);
        $meta['vibe_score'] = $vibeScore;
        $meta['vibe_metrics'] = $metrics;
        $meta['vibe_refreshed_at'] = now()->toIso8601String();

        $this->setMeta($world, $meta);
        $world->ambience_state = $ambienceState;
        $world->save();

        return $world->fresh();
    }

    public function applyWarmthBoost(Couple $couple): ?World
    {
        $world = $couple->world;
        if (! $world) {
            return null;
        }

        $meta = $this->meta($world);
        $hours = (int) config('world.vibe.warmth_boost_hours', 6);
        $meta['warmth_boost_until'] = now()->addHours($hours)->toIso8601String();
        $this->setMeta($world, $meta);
        $world->save();

        return $this->refreshForCouple($couple);
    }

    public function applyCoachGlow(Couple $couple): ?World
    {
        $world = $couple->world;
        if (! $world) {
            return null;
        }

        $meta = $this->meta($world);
        $minutes = (int) config('world.vibe.coach_glow_minutes', 20);
        $meta['coach_glow_until'] = now()->addMinutes($minutes)->toIso8601String();
        $this->setMeta($world, $meta);
        $world->save();

        return $this->refreshForCouple($couple);
    }

    protected function vibeMetrics(Couple $couple): array
    {
        $windowDays = (int) config('world.vibe.recent_days', 7);
        $from = now()->subDays($windowDays);

        $avgMood = (float) MoodCheckin::where('couple_id', $couple->id)
            ->where('date', '>=', $from->toDateString())
            ->avg('mood_level');

        $moodScore = $avgMood > 0 ? (int) round(($avgMood / 5) * 100) : 55;

        $xpWindowTotal = (int) $couple->xpEvents()
            ->where('created_at', '>=', $from)
            ->sum('xp_amount');
        $xpTarget = max(1, (int) config('world.vibe.xp_target_per_window', 140));
        $xpRateScore = (int) min(100, round(($xpWindowTotal / $xpTarget) * 100));

        $repairCompletions = (int) RepairSession::where('couple_id', $couple->id)
            ->where('status', 'completed')
            ->where('completed_at', '>=', $from)
            ->count();
        $repairBonusPerCompletion = max(1, (int) config('world.vibe.repair_bonus_per_completion', 40));
        $repairScore = (int) min(100, $repairCompletions * $repairBonusPerCompletion);

        return [
            'mood_score' => $moodScore,
            'xp_rate_score' => $xpRateScore,
            'repair_score' => $repairScore,
        ];
    }

    protected function vibeScore(array $metrics, World $world): int
    {
        $weights = config('world.vibe.weights', []);
        $score = (int) round(
            ($metrics['mood_score'] * ((float) ($weights['mood'] ?? 0.5))) +
            ($metrics['xp_rate_score'] * ((float) ($weights['xp_rate'] ?? 0.35))) +
            ($metrics['repair_score'] * ((float) ($weights['repair'] ?? 0.15)))
        );

        if ($this->isWarmthBoostActive($world)) {
            $score += (int) config('world.vibe.warmth_boost_points', 12);
        }

        return min(100, max(0, $score));
    }

    protected function ambienceFromScore(int $score): string
    {
        $bright = (int) config('world.vibe.thresholds.bright', 68);
        $calm = (int) config('world.vibe.thresholds.calm', 42);

        if ($score >= $bright) {
            return 'bright';
        }

        if ($score >= $calm) {
            return 'calm';
        }

        return 'quiet';
    }

    protected function isWarmthBoostActive(World $world): bool
    {
        $until = $this->meta($world)['warmth_boost_until'] ?? null;
        if (! $until) {
            return false;
        }

        return Carbon::parse($until)->isFuture();
    }

    protected function meta(World $world): array
    {
        $cosmetics = $world->cosmetics ?? [];

        return (array) ($cosmetics['__meta'] ?? []);
    }

    protected function setMeta(World $world, array $meta): void
    {
        $cosmetics = $world->cosmetics ?? [];
        $cosmetics['__meta'] = $meta;
        $world->cosmetics = $cosmetics;
    }
}
