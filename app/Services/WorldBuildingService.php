<?php

namespace App\Services;

use App\Models\Couple;
use App\Models\CoupleWallet;
use App\Models\Memory;
use App\Models\MissionCompletion;
use App\Models\MoodCheckin;
use App\Models\User;
use App\Models\World;
use App\Models\WorldItem;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class WorldBuildingService
{
    public function __construct(
        protected WorldCatalogService $catalogService
    ) {}

    public function getWorldState(Couple $couple, User $user): array
    {
        $world = $this->authorizeWorldAccess($couple, $user);
        $wallet = CoupleWallet::firstOrCreate(
            ['couple_id' => $couple->id],
            ['love_seeds_balance' => config('world.starting_love_seeds', 0)]
        );

        return [
            'world' => $world,
            'wallet' => $wallet,
            'items' => WorldItem::where('couple_id', $couple->id)->get(),
            'catalog' => $this->catalogService->itemsForWorldType($world->resolvedWorldType()),
        ];
    }

    public function initializeStarterState(Couple $couple, World $world): void
    {
        CoupleWallet::firstOrCreate(
            ['couple_id' => $couple->id],
            ['love_seeds_balance' => config('world.starting_love_seeds', 0)]
        );

        $worldType = $world->resolvedWorldType();
        $starterItems = $this->catalogService->starterItemsForWorldType($worldType);

        foreach ($starterItems as $itemKey => $definition) {
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
     * @throws ValidationException
     */
    public function purchaseOrUpgradeItem(Couple $couple, User $user, string $itemKey): WorldItem
    {
        $world = $this->authorizeWorldAccess($couple, $user);
        $itemDefinition = $this->catalogService->getItem($itemKey);

        if (! $itemDefinition) {
            throw ValidationException::withMessages([
                'item_key' => 'Unknown world item.',
            ]);
        }

        $worldType = $world->resolvedWorldType();
        if (! in_array($worldType, $itemDefinition['world_types'] ?? [], true)) {
            throw ValidationException::withMessages([
                'item_key' => 'Item is not compatible with this world type.',
            ]);
        }

        return DB::transaction(function () use ($couple, $world, $itemKey, $itemDefinition) {
            $lockedWorld = World::whereKey($world->id)->lockForUpdate()->firstOrFail();

            $wallet = CoupleWallet::where('couple_id', $couple->id)->lockForUpdate()->first();
            if (! $wallet) {
                $wallet = CoupleWallet::create([
                    'couple_id' => $couple->id,
                    'love_seeds_balance' => config('world.starting_love_seeds', 0),
                ]);
            }

            $worldItem = WorldItem::where('couple_id', $couple->id)
                ->where('item_key', $itemKey)
                ->lockForUpdate()
                ->first();

            if (! $worldItem) {
                $worldItem = WorldItem::create([
                    'couple_id' => $couple->id,
                    'world_type' => $lockedWorld->resolvedWorldType(),
                    'item_key' => $itemKey,
                    'level' => 0,
                    'is_built' => false,
                ]);
            }

            $nextLevel = $worldItem->level + 1;
            $maxLevel = (int) ($itemDefinition['max_level'] ?? 1);
            if ($nextLevel > $maxLevel) {
                throw ValidationException::withMessages([
                    'item_key' => 'Item is already at maximum level.',
                ]);
            }

            $this->validateUnlockRequirements($couple, $lockedWorld, $itemDefinition);

            $cost = $itemDefinition['costs'][$nextLevel] ?? null;
            if (! is_array($cost)) {
                throw ValidationException::withMessages([
                    'item_key' => 'Missing upgrade cost configuration.',
                ]);
            }

            $xpCost = (int) ($cost['xp'] ?? 0);
            $loveSeedsCost = (int) ($cost['love_seeds'] ?? 0);

            if ($xpCost > $lockedWorld->xp_total) {
                throw ValidationException::withMessages([
                    'xp' => 'Not enough XP for this upgrade.',
                ]);
            }

            if ($loveSeedsCost > $wallet->love_seeds_balance) {
                throw ValidationException::withMessages([
                    'love_seeds' => 'Not enough Love Seeds for this upgrade.',
                ]);
            }

            if ($xpCost > 0) {
                $lockedWorld->spendXp($xpCost);
            }

            if ($loveSeedsCost > 0) {
                $wallet->love_seeds_balance -= $loveSeedsCost;
                $wallet->save();
            }

            $worldItem->level = $nextLevel;
            $worldItem->is_built = $nextLevel > 0;
            $worldItem->world_type = $lockedWorld->resolvedWorldType();
            $worldItem->save();

            return $worldItem->fresh();
        });
    }

    /**
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function placeItem(
        Couple $couple,
        User $user,
        string $itemKey,
        string $slot,
        ?array $position = null
    ): WorldItem {
        $this->authorizeWorldAccess($couple, $user);

        $item = WorldItem::where('couple_id', $couple->id)
            ->where('item_key', $itemKey)
            ->first();

        if (! $item || ! $item->is_built) {
            throw ValidationException::withMessages([
                'item_key' => 'Only built items can be placed.',
            ]);
        }

        Gate::forUser($user)->authorize('update', $item);

        $item->slot = $slot;
        $item->position = $position;
        $item->save();

        return $item->fresh();
    }

    /**
     * @throws AuthorizationException
     */
    public function getMemoryFrameHighlight(Couple $couple, User $user): ?array
    {
        $this->authorizeWorldAccess($couple, $user);

        $memory = Memory::where('couple_id', $couple->id)
            ->where('visibility', 'shared')
            ->where('comfort', true)
            ->inRandomOrder()
            ->first();

        if (! $memory) {
            return null;
        }

        return [
            'id' => $memory->id,
            'title' => $memory->title ?: 'Comfort memory',
            'thumbnail_url' => $memory->getThumbnailUrl() ?: $memory->getFileUrl(),
            'type' => $memory->type,
        ];
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
    protected function validateUnlockRequirements(Couple $couple, World $world, array $itemDefinition): void
    {
        $requirements = $itemDefinition['unlocks'] ?? [];
        $requiredWorldLevel = (int) ($requirements['world_level'] ?? 1);
        if ($world->level < $requiredWorldLevel) {
            throw ValidationException::withMessages([
                'world_level' => "Requires world level {$requiredWorldLevel}.",
            ]);
        }

        $requiredCompletions = (int) ($requirements['mission_completions'] ?? 0);
        if ($requiredCompletions > 0) {
            $actualCompletions = MissionCompletion::whereHas(
                'assignment',
                fn ($query) => $query->where('couple_id', $couple->id)
            )->count();

            if ($actualCompletions < $requiredCompletions) {
                throw ValidationException::withMessages([
                    'mission_completions' => "Requires {$requiredCompletions} mission completions.",
                ]);
            }
        }

        $requiredStreak = (int) ($requirements['streak_days'] ?? 0);
        if ($requiredStreak > 0 && $this->currentMoodStreakDays($couple) < $requiredStreak) {
            throw ValidationException::withMessages([
                'streak_days' => "Requires a {$requiredStreak}-day check-in streak.",
            ]);
        }
    }

    protected function currentMoodStreakDays(Couple $couple): int
    {
        $checkinDates = MoodCheckin::where('couple_id', $couple->id)
            ->orderByDesc('date')
            ->pluck('date')
            ->map(fn ($date) => Carbon::parse($date)->toDateString())
            ->unique()
            ->values();

        if ($checkinDates->isEmpty()) {
            return 0;
        }

        $streak = 0;
        $cursor = now()->toDateString();

        foreach ($checkinDates as $date) {
            if ($date !== $cursor) {
                break;
            }

            $streak++;
            $cursor = Carbon::parse($cursor)->subDay()->toDateString();
        }

        return $streak;
    }
}
