<?php

namespace App\Services\WorldV2;

use App\Models\Couple;
use App\Models\CoupleWallet;
use App\Models\MissionCompletion;
use App\Models\MoodCheckin;
use App\Models\RepairSession;
use App\Models\User;
use App\Models\World;
use App\Models\WorldItem;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class WorldService
{
    public function stateFor(Couple $couple, User $user): array
    {
        $world = $this->authorizeWorldAccess($couple, $user);
        $worldType = $this->normalizeWorldType($world->resolvedWorldType());

        $wallet = CoupleWallet::firstOrCreate(
            ['couple_id' => $couple->id],
            ['love_seeds_balance' => (int) config('world_v2.starting_love_seeds', 120)]
        );

        $this->initializeStarterItems($couple, $worldType);

        return [
            'world' => $world->fresh(),
            'wallet' => $wallet->fresh(),
            'world_type' => $worldType,
            'slots' => $this->slotsForWorldType($worldType),
            'catalog' => $this->catalogForWorldType($worldType),
            'items' => WorldItem::where('couple_id', $couple->id)->get(),
        ];
    }

    /**
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function buyOrUpgrade(Couple $couple, User $user, string $itemKey): WorldItem
    {
        $world = $this->authorizeWorldAccess($couple, $user);
        $worldType = $this->normalizeWorldType($world->resolvedWorldType());
        $catalogItem = $this->catalogItem($itemKey);

        if (! $catalogItem) {
            throw ValidationException::withMessages(['item_key' => 'Unknown world item.']);
        }

        if (! in_array($worldType, $catalogItem['world_types'] ?? [], true)) {
            throw ValidationException::withMessages(['item_key' => 'Item is not compatible with this world type.']);
        }

        return DB::transaction(function () use ($couple, $world, $worldType, $itemKey, $catalogItem) {
            $lockedWorld = World::whereKey($world->id)->lockForUpdate()->firstOrFail();
            $wallet = CoupleWallet::where('couple_id', $couple->id)->lockForUpdate()->first();

            if (! $wallet) {
                $wallet = CoupleWallet::create([
                    'couple_id' => $couple->id,
                    'love_seeds_balance' => (int) config('world_v2.starting_love_seeds', 120),
                ]);
            }

            $worldItem = WorldItem::where('couple_id', $couple->id)
                ->where('item_key', $itemKey)
                ->lockForUpdate()
                ->first();

            if (! $worldItem) {
                $worldItem = WorldItem::create([
                    'couple_id' => $couple->id,
                    'world_type' => $worldType,
                    'item_key' => $itemKey,
                    'level' => 0,
                    'slot' => $catalogItem['default_slot'] ?? null,
                    'is_built' => false,
                ]);
            }

            $nextLevel = ((int) $worldItem->level) + 1;
            $maxLevel = (int) ($catalogItem['max_level'] ?? 1);
            if ($nextLevel > $maxLevel) {
                throw ValidationException::withMessages(['item_key' => 'Item is already at maximum level.']);
            }

            $this->assertUnlockRequirements($couple, $lockedWorld, $catalogItem);

            $cost = $catalogItem['costs'][$nextLevel] ?? null;
            if (! is_array($cost)) {
                throw ValidationException::withMessages(['item_key' => 'Missing upgrade cost configuration.']);
            }

            $loveSeedsCost = (int) ($cost['love_seeds'] ?? 0);
            if ($loveSeedsCost > (int) $wallet->love_seeds_balance) {
                throw ValidationException::withMessages(['love_seeds' => 'Not enough Love Seeds for this upgrade.']);
            }

            if ($loveSeedsCost > 0) {
                $wallet->decrement('love_seeds_balance', $loveSeedsCost);
            }

            $worldItem->fill([
                'world_type' => $worldType,
                'level' => $nextLevel,
                'is_built' => true,
            ]);
            $worldItem->save();

            return $worldItem->fresh();
        });
    }

    /**
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function placeItem(Couple $couple, User $user, string $itemKey, string $slot, ?array $position = null): WorldItem
    {
        $world = $this->authorizeWorldAccess($couple, $user);
        $worldType = $this->normalizeWorldType($world->resolvedWorldType());

        if (! in_array($slot, $this->slotsForWorldType($worldType), true)) {
            throw ValidationException::withMessages(['slot' => 'Invalid slot for this world type.']);
        }

        $item = WorldItem::where('couple_id', $couple->id)
            ->where('item_key', $itemKey)
            ->first();

        if (! $item || ! $item->is_built) {
            throw ValidationException::withMessages(['item_key' => 'Only built items can be placed.']);
        }

        Gate::forUser($user)->authorize('update', $item);

        $item->slot = $slot;
        $item->position = $position;
        $item->save();

        return $item->fresh();
    }

    public function refreshVibe(Couple $couple): ?World
    {
        $world = $couple->world;
        if (! $world) {
            return null;
        }

        $score = $this->vibeScore($couple);
        $meta = $this->meta($world);
        $meta['vibe_score'] = $score;
        $meta['vibe_refreshed_at'] = now()->toIso8601String();
        $meta['vibe_metrics'] = $this->vibeMetrics($couple);

        $cosmetics = $world->cosmetics ?? [];
        $cosmetics['__meta'] = $meta;

        $world->cosmetics = $cosmetics;
        $world->ambience_state = $this->ambienceFromScore($score);
        $world->save();

        return $world->fresh();
    }

    public function catalogForWorldType(string $worldType): array
    {
        $type = $this->normalizeWorldType($worldType);

        return array_filter(
            config('world_v2.catalog', []),
            fn (array $item) => in_array($type, $item['world_types'] ?? [], true)
        );
    }

    public function slotsForWorldType(string $worldType): array
    {
        $type = $this->normalizeWorldType($worldType);

        return (array) Arr::get(config('world_v2.world_types', []), $type.'.slots', []);
    }

    public function normalizeWorldType(string $worldType): string
    {
        return match ($worldType) {
            'house' => 'cottage',
            default => in_array($worldType, ['garden', 'cottage', 'kitchen'], true) ? $worldType : 'garden',
        };
    }

    protected function catalogItem(string $itemKey): ?array
    {
        return config('world_v2.catalog.'.$itemKey);
    }

    protected function initializeStarterItems(Couple $couple, string $worldType): void
    {
        $catalog = $this->catalogForWorldType($worldType);

        foreach ($catalog as $itemKey => $definition) {
            if (! (bool) ($definition['starter'] ?? false)) {
                continue;
            }

            WorldItem::firstOrCreate(
                [
                    'couple_id' => $couple->id,
                    'item_key' => $itemKey,
                ],
                [
                    'world_type' => $worldType,
                    'level' => 1,
                    'slot' => $definition['default_slot'] ?? null,
                    'is_built' => true,
                ]
            );
        }
    }

    /**
     * @throws AuthorizationException
     */
    protected function authorizeWorldAccess(Couple $couple, User $user): World
    {
        $world = $couple->world()->firstOrFail();
        Gate::forUser($user)->authorize('view', $world);

        return $world;
    }

    /**
     * @throws ValidationException
     */
    protected function assertUnlockRequirements(Couple $couple, World $world, array $itemDefinition): void
    {
        $requirements = $itemDefinition['unlocks'] ?? [];

        $requiredWorldLevel = (int) ($requirements['world_level'] ?? 1);
        if ((int) $world->level < $requiredWorldLevel) {
            throw ValidationException::withMessages([
                'world_level' => "Requires world level {$requiredWorldLevel}.",
            ]);
        }

        $requiredCompletions = (int) ($requirements['mission_completions'] ?? 0);
        if ($requiredCompletions > 0) {
            $completions = MissionCompletion::whereHas(
                'assignment',
                fn ($query) => $query->where('couple_id', $couple->id)
            )->count();

            if ($completions < $requiredCompletions) {
                throw ValidationException::withMessages([
                    'mission_completions' => "Requires {$requiredCompletions} mission completions.",
                ]);
            }
        }

        $requiredStreak = (int) ($requirements['streak_days'] ?? 0);
        if ($requiredStreak > 0 && $this->checkinStreak($couple) < $requiredStreak) {
            throw ValidationException::withMessages([
                'streak_days' => "Requires a {$requiredStreak}-day check-in streak.",
            ]);
        }
    }

    protected function checkinStreak(Couple $couple): int
    {
        $dates = MoodCheckin::where('couple_id', $couple->id)
            ->orderByDesc('date')
            ->pluck('date')
            ->map(fn ($date) => Carbon::parse($date)->toDateString())
            ->unique()
            ->values();

        if ($dates->isEmpty()) {
            return 0;
        }

        $cursor = now()->toDateString();
        $streak = 0;

        foreach ($dates as $date) {
            if ($date !== $cursor) {
                break;
            }

            $streak++;
            $cursor = Carbon::parse($cursor)->subDay()->toDateString();
        }

        return $streak;
    }

    protected function vibeScore(Couple $couple): int
    {
        $metrics = $this->vibeMetrics($couple);
        $weights = config('world_v2.vibe.weights', []);

        $score = (int) round(
            ($metrics['mood_score'] * ((float) ($weights['mood'] ?? 0.5))) +
            ($metrics['xp_rate_score'] * ((float) ($weights['xp_rate'] ?? 0.35))) +
            ($metrics['repair_score'] * ((float) ($weights['repair'] ?? 0.15)))
        );

        return max(0, min(100, $score));
    }

    protected function vibeMetrics(Couple $couple): array
    {
        $windowDays = (int) config('world_v2.vibe.recent_days', 7);
        $from = now()->subDays($windowDays);

        $avgMood = (float) MoodCheckin::where('couple_id', $couple->id)
            ->where('date', '>=', $from->toDateString())
            ->avg('mood_level');

        $moodScore = $avgMood > 0 ? (int) round(($avgMood / 5) * 100) : 55;

        $xpTotal = (int) $couple->xpEvents()
            ->where('created_at', '>=', $from)
            ->sum('xp_amount');

        $xpTarget = max(1, (int) config('world_v2.vibe.xp_target_per_window', 140));
        $xpRateScore = (int) min(100, round(($xpTotal / $xpTarget) * 100));

        $repairCompletions = (int) RepairSession::where('couple_id', $couple->id)
            ->where('status', 'completed')
            ->where('completed_at', '>=', $from)
            ->count();

        $repairBonus = max(1, (int) config('world_v2.vibe.repair_bonus_per_completion', 40));
        $repairScore = (int) min(100, $repairCompletions * $repairBonus);

        return [
            'mood_score' => $moodScore,
            'xp_rate_score' => $xpRateScore,
            'repair_score' => $repairScore,
        ];
    }

    protected function ambienceFromScore(int $score): string
    {
        $bright = (int) config('world_v2.vibe.thresholds.bright', 68);
        $calm = (int) config('world_v2.vibe.thresholds.calm', 42);

        if ($score >= $bright) {
            return 'bright';
        }

        if ($score >= $calm) {
            return 'calm';
        }

        return 'quiet';
    }

    protected function meta(World $world): array
    {
        $cosmetics = $world->cosmetics ?? [];

        return (array) ($cosmetics['__meta'] ?? []);
    }
}
