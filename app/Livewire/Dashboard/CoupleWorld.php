<?php

namespace App\Livewire\Dashboard;

use App\Models\MissionCompletion;
use App\Models\MoodCheckin;
use App\Services\CoupleService;
use App\Services\WorldBuildingService;
use App\Services\WorldVibeService;
use App\Services\XpService;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class CoupleWorld extends Component
{
    public $couple;

    public $world;

    public $recentXpEvents;

    public $todayXp;

    public $xpForNextLevel;

    public $levelProgress;

    public $walletBalance = 0;

    public $catalog = [];

    public $items = [];

    public $shopOpen = false;

    public $shopSearch = '';

    public $shopCategory = 'all';

    public $selectedItemKey = null;

    public $selectedItemData = null;

    public $missionCompletions = 0;

    public $checkinStreak = 0;

    public $placementMode = false;

    public $placementItemKey = null;

    public $memoryFrameHighlight = null;

    public $vibeScore = 0;

    public $warmthBoostActive = false;

    public $coachGlowActive = false;

    public function mount()
    {
        $coupleService = app(CoupleService::class);
        $this->couple = $coupleService->getUserCouple(auth()->user());

        if ($this->couple) {
            $this->loadWorldState();
        }
    }

    public function toggleShop(): void
    {
        $this->shopOpen = ! $this->shopOpen;
        $this->resetErrorBag();
    }

    public function openUpgradeModal(string $itemKey): void
    {
        if (! isset($this->catalog[$itemKey])) {
            return;
        }

        $this->selectedItemKey = $itemKey;
        $this->selectedItemData = $this->catalog[$itemKey];
        $this->resetErrorBag();
    }

    public function closeUpgradeModal(): void
    {
        $this->selectedItemKey = null;
        $this->selectedItemData = null;
        $this->resetErrorBag();
    }

    public function upgradeSelectedItem(): void
    {
        if (! $this->couple || ! $this->selectedItemKey) {
            return;
        }

        try {
            app(WorldBuildingService::class)->purchaseOrUpgradeItem(
                $this->couple,
                auth()->user(),
                $this->selectedItemKey
            );
        } catch (ValidationException $exception) {
            $messages = $exception->errors();
            $message = collect($messages)->flatten()->first() ?? 'Upgrade could not be completed.';
            $this->addError('upgrade', $message);

            return;
        }

        $upgradedItem = $this->selectedItemKey;
        $this->closeUpgradeModal();
        $this->loadWorldState();
        $this->dispatch('world-item-upgraded', itemKey: $upgradedItem);
    }

    public function startPlacementMode(string $itemKey): void
    {
        if (! isset($this->items[$itemKey]) || ! ($this->items[$itemKey]['is_built'] ?? false)) {
            return;
        }

        $this->placementMode = true;
        $this->placementItemKey = $itemKey;
        $this->closeUpgradeModal();
    }

    public function cancelPlacementMode(): void
    {
        $this->placementMode = false;
        $this->placementItemKey = null;
    }

    public function placeSelectedItemAt(string $slot): void
    {
        if (! $this->placementMode || ! $this->placementItemKey || ! isset($this->slotPositions()[$slot])) {
            return;
        }

        try {
            app(WorldBuildingService::class)->placeItem(
                $this->couple,
                auth()->user(),
                $this->placementItemKey,
                $slot
            );
        } catch (ValidationException $exception) {
            $messages = $exception->errors();
            $message = collect($messages)->flatten()->first() ?? 'Unable to place this item.';
            $this->addError('upgrade', $message);

            return;
        }

        $placedItem = $this->placementItemKey;
        $this->cancelPlacementMode();
        $this->loadWorldState();
        $this->dispatch('world-item-placed', itemKey: $placedItem);
    }

    public function sceneSlotClass(string $itemKey): string
    {
        $slots = array_values($this->slotPositions());
        $itemState = $this->items[$itemKey] ?? null;
        $savedSlot = $itemState['slot'] ?? null;
        if ($savedSlot && isset($this->slotPositions()[$savedSlot])) {
            return $this->slotPositions()[$savedSlot];
        }

        $index = abs(crc32($itemKey)) % count($slots);

        return $slots[$index];
    }

    public function slotPositions(): array
    {
        return [
            'slot_a' => 'left-[9%] bottom-[18%]',
            'slot_b' => 'left-[22%] bottom-[25%]',
            'slot_c' => 'left-[36%] bottom-[14%]',
            'slot_d' => 'left-[49%] bottom-[24%]',
            'slot_e' => 'left-[61%] bottom-[15%]',
            'slot_f' => 'left-[73%] bottom-[24%]',
            'slot_g' => 'left-[84%] bottom-[17%]',
            'slot_h' => 'left-[43%] bottom-[35%]',
        ];
    }

    public function nextCostFor(string $itemKey): ?array
    {
        $item = $this->items[$itemKey] ?? null;
        $definition = $this->catalog[$itemKey] ?? null;
        if (! $definition) {
            return null;
        }

        $nextLevel = ($item['level'] ?? 0) + 1;

        return $definition['costs'][$nextLevel] ?? null;
    }

    public function unlockTextFor(string $itemKey): string
    {
        $definition = $this->catalog[$itemKey] ?? null;
        if (! $definition) {
            return 'Unknown unlock requirements.';
        }

        $requirements = $definition['unlocks'] ?? [];
        $parts = [];

        if (isset($requirements['world_level'])) {
            $parts[] = 'World Lv '.$requirements['world_level'];
        }
        if (isset($requirements['mission_completions'])) {
            $parts[] = $requirements['mission_completions'].' mission completions';
        }
        if (isset($requirements['streak_days'])) {
            $parts[] = $requirements['streak_days'].'-day streak';
        }

        return empty($parts) ? 'No unlock requirements.' : implode(' | ', $parts);
    }

    public function isLocked(string $itemKey): bool
    {
        $definition = $this->catalog[$itemKey] ?? null;
        if (! $definition) {
            return true;
        }

        $requirements = $definition['unlocks'] ?? [];
        if (($this->world?->level ?? 0) < (int) ($requirements['world_level'] ?? 1)) {
            return true;
        }
        if ($this->missionCompletions < (int) ($requirements['mission_completions'] ?? 0)) {
            return true;
        }
        if ($this->checkinStreak < (int) ($requirements['streak_days'] ?? 0)) {
            return true;
        }

        return false;
    }

    public function filteredCatalog(): array
    {
        return array_filter($this->catalog, function (array $definition) {
            $name = strtolower($definition['name'] ?? '');
            $search = strtolower(trim($this->shopSearch));
            $matchesSearch = $search === '' || str_contains($name, $search);
            $matchesCategory = $this->shopCategory === 'all' || ($definition['category'] ?? '') === $this->shopCategory;

            return $matchesSearch && $matchesCategory;
        });
    }

    public function catalogCategories(): array
    {
        $categories = collect($this->catalog)->pluck('category')->filter()->unique()->sort()->values()->all();

        return array_merge(['all'], $categories);
    }

    public function render()
    {
        return view('livewire.dashboard.couple-world', [
            'filteredCatalog' => $this->filteredCatalog(),
            'categories' => $this->catalogCategories(),
        ])->layout('layouts.app');
    }

    protected function loadWorldState(): void
    {
        $this->couple = $this->couple->fresh();
        app(WorldVibeService::class)->refreshForCouple($this->couple);
        $state = app(WorldBuildingService::class)->getWorldState($this->couple, auth()->user());

        $this->world = $state['world'];
        $this->walletBalance = $state['wallet']->love_seeds_balance;
        $this->catalog = $state['catalog'];
        $this->items = collect($state['items'])
            ->keyBy('item_key')
            ->map(fn ($item) => [
                'level' => $item->level,
                'is_built' => $item->is_built,
                'slot' => $item->slot,
                'position' => $item->position,
            ])
            ->toArray();

        $xpService = app(XpService::class);
        $this->recentXpEvents = $xpService->getXpHistory($this->couple, 10);
        $this->todayXp = $xpService->getTodayXp($this->couple);

        if ($this->world) {
            $this->xpForNextLevel = $xpService->xpForNextLevel($this->world->level);
            $currentLevelXp = $this->world->xp_total % $this->xpForNextLevel;
            $this->levelProgress = ($currentLevelXp / $this->xpForNextLevel) * 100;
        }

        $this->missionCompletions = MissionCompletion::whereHas(
            'assignment',
            fn ($query) => $query->where('couple_id', $this->couple->id)
        )->count();
        $this->checkinStreak = $this->calculateCheckinStreak();

        $memoryFrameBuilt = collect($this->items)->contains(function (array $item, string $itemKey) {
            return str_ends_with($itemKey, 'memory_frame') && ($item['level'] ?? 0) > 0;
        });
        $this->memoryFrameHighlight = $memoryFrameBuilt
            ? app(WorldBuildingService::class)->getMemoryFrameHighlight($this->couple, auth()->user())
            : null;

        $meta = (array) (($this->world->cosmetics ?? [])['__meta'] ?? []);
        $this->vibeScore = (int) ($meta['vibe_score'] ?? 0);
        $this->warmthBoostActive = ! empty($meta['warmth_boost_until']) && Carbon::parse($meta['warmth_boost_until'])->isFuture();
        $this->coachGlowActive = ! empty($meta['coach_glow_until']) && Carbon::parse($meta['coach_glow_until'])->isFuture();
    }

    protected function calculateCheckinStreak(): int
    {
        $dates = MoodCheckin::where('couple_id', $this->couple->id)
            ->orderByDesc('date')
            ->pluck('date')
            ->map(fn ($date) => Carbon::parse($date)->toDateString())
            ->unique()
            ->values();

        if ($dates->isEmpty()) {
            return 0;
        }

        $streak = 0;
        $cursor = today();

        foreach ($dates as $date) {
            if ($date !== $cursor->toDateString()) {
                break;
            }

            $streak++;
            $cursor = $cursor->copy()->subDay();
        }

        return $streak;
    }
}
