<div class="min-h-screen bg-slate-100">
    <style>
        .world-scene {
            background:
                radial-gradient(circle at 12% 18%, rgba(255, 255, 255, 0.45), transparent 36%),
                radial-gradient(circle at 88% 20%, rgba(252, 211, 77, 0.24), transparent 30%),
                linear-gradient(180deg, #0f172a 0%, #1e293b 46%, #334155 100%);
        }

        .world-ground {
            background:
                radial-gradient(circle at 50% 0%, rgba(255, 255, 255, 0.2), transparent 50%),
                linear-gradient(180deg, rgba(30, 41, 59, 0) 0%, rgba(15, 23, 42, 0.38) 100%);
        }

        @keyframes itemPulse {
            0% { transform: scale(1); }
            45% { transform: scale(1.08); }
            100% { transform: scale(1); }
        }

        .item-pulse {
            animation: itemPulse 420ms ease-out;
        }
    </style>

    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8"
        x-data="{ driftX: 0, driftY: 0, pulseKey: null, placementKey: null }"
        x-on:world-item-upgraded.window="pulseKey = $event.detail.itemKey; setTimeout(() => pulseKey = null, 700)"
        x-on:world-item-placed.window="placementKey = $event.detail.itemKey; setTimeout(() => placementKey = null, 700)"
        @mousemove="driftX = (($event.clientX / window.innerWidth) - 0.5) * 12; driftY = (($event.clientY / window.innerHeight) - 0.5) * 8">
        @if($couple && $world)
            <div class="mb-4 rounded-3xl border border-slate-200 bg-white/95 p-4 shadow-sm sm:p-5">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-slate-500">Hi, {{ auth()->user()->name }}</p>
                        <h1 class="text-2xl font-semibold text-slate-900">Shared World</h1>
                        <p class="text-sm text-slate-600">Today's vibe: {{ ucfirst($world->ambience_state) }}</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3 text-sm">
                        <div class="rounded-xl bg-amber-100 px-3 py-2 font-semibold text-amber-800">
                            Love Seeds: {{ number_format($walletBalance) }}
                        </div>
                        <div class="rounded-xl bg-slate-100 px-3 py-2 font-semibold text-slate-700">
                            Streak: {{ $checkinStreak }} days
                        </div>
                        <button wire:click="toggleShop"
                            class="rounded-xl bg-slate-900 px-4 py-2 font-semibold text-white transition hover:bg-slate-700">
                            {{ $shopOpen ? 'Close Build Menu' : 'Open Build Menu' }}
                        </button>
                        @if($placementMode && $placementItemKey)
                            <button wire:click="cancelPlacementMode"
                                class="rounded-xl bg-rose-100 px-4 py-2 font-semibold text-rose-700 transition hover:bg-rose-200">
                                Cancel Placement
                            </button>
                        @endif
                    </div>
                </div>
                <div class="mt-4">
                    <div class="mb-2 flex items-center justify-between text-sm">
                        <p class="font-semibold text-slate-700">World Level {{ $world->level }}</p>
                        <p class="text-slate-500">{{ number_format($xpForNextLevel - ($world->xp_total % $xpForNextLevel)) }} XP to next level</p>
                    </div>
                    <div class="h-3 overflow-hidden rounded-full bg-slate-200">
                        <div class="h-full rounded-full bg-gradient-to-r from-sky-500 via-emerald-400 to-amber-300 transition-all duration-700"
                            style="width: {{ $levelProgress }}%"></div>
                    </div>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-3xl border border-slate-200 shadow-2xl">
                <div class="world-scene relative h-[480px] w-full overflow-hidden sm:h-[560px]">
                    <div class="pointer-events-none absolute inset-0 transition-transform duration-300"
                        :style="`transform: translate(${driftX * 0.35}px, ${driftY * 0.35}px)`">
                        <div class="absolute left-[10%] top-[12%] h-14 w-14 rounded-full bg-white/30 blur-sm"></div>
                        <div class="absolute right-[12%] top-[18%] h-12 w-12 rounded-full bg-amber-200/40 blur-sm"></div>
                        <div class="absolute left-[50%] top-[10%] h-2 w-2 rounded-full bg-white/70"></div>
                    </div>

                    <div class="pointer-events-none absolute inset-x-0 bottom-0 h-[48%] world-ground"></div>

                    <div class="absolute inset-x-0 bottom-0 h-[52%]">
                        @if($placementMode && $placementItemKey)
                            @foreach($this->slotPositions() as $slotKey => $slotClass)
                                <button wire:click="placeSelectedItemAt('{{ $slotKey }}')"
                                    class="absolute {{ $slotClass }} h-9 w-9 rounded-full border-2 border-dashed border-white/80 bg-sky-300/30 shadow-lg transition hover:scale-110 hover:bg-sky-300/60"
                                    title="Place here"></button>
                            @endforeach
                        @endif

                        @foreach($catalog as $itemKey => $definition)
                            @php
                                $itemState = $items[$itemKey] ?? null;
                                $level = $itemState['level'] ?? 0;
                                $nextCost = $this->nextCostFor($itemKey);
                                $locked = $this->isLocked($itemKey);
                            @endphp

                            @if($level > 0 || ($definition['starter'] ?? false))
                                <button wire:click="openUpgradeModal('{{ $itemKey }}')"
                                    class="group absolute {{ $this->sceneSlotClass($itemKey) }} w-28 rounded-2xl border border-white/30 bg-white/15 p-2 text-left text-white shadow-xl backdrop-blur transition hover:scale-105 hover:bg-white/25 sm:w-32"
                                    :class="{ 'item-pulse': pulseKey === '{{ $itemKey }}' || placementKey === '{{ $itemKey }}' }">
                                    <p class="truncate text-xs font-semibold uppercase tracking-wide text-slate-100">{{ $definition['category'] }}</p>
                                    <p class="truncate text-sm font-semibold">{{ $definition['name'] }}</p>
                                    <p class="text-xs text-slate-100/85">Lv {{ $level }}</p>

                                    <div
                                        class="pointer-events-none absolute -top-20 left-1/2 z-20 hidden w-48 -translate-x-1/2 rounded-xl border border-slate-200 bg-white p-2 text-xs text-slate-700 shadow-lg group-hover:block">
                                        <p class="font-semibold text-slate-900">{{ $definition['name'] }}</p>
                                        @if($nextCost)
                                            <p>Next cost:
                                                {{ isset($nextCost['love_seeds']) ? $nextCost['love_seeds'].' seeds' : '' }}
                                                {{ isset($nextCost['xp']) ? '| '.$nextCost['xp'].' XP' : '' }}
                                            </p>
                                        @else
                                            <p>At max level.</p>
                                        @endif
                                    </div>
                                </button>
                            @endif
                        @endforeach
                    </div>

                    <div class="absolute left-4 top-4 rounded-xl bg-black/25 px-3 py-2 text-xs font-medium text-white">
                        {{ ucfirst($world->resolvedWorldType()) }} world
                    </div>
                    @if($placementMode && $placementItemKey)
                        <div class="absolute right-4 top-4 rounded-xl bg-sky-100 px-3 py-2 text-xs font-semibold text-sky-800">
                            Placement mode: choose a slot for {{ $catalog[$placementItemKey]['name'] ?? 'item' }}
                        </div>
                    @endif
                </div>
            </div>

            <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-5">
                <a href="/checkin" class="rounded-2xl bg-white p-4 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-sky-50">Daily Check-in</a>
                <a href="/missions" class="rounded-2xl bg-white p-4 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-emerald-50">Missions Board</a>
                <a href="{{ route('repair.history') }}" class="rounded-2xl bg-white p-4 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-amber-50">Repair</a>
                <a href="{{ route('vault.gallery') }}" class="rounded-2xl bg-white p-4 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-rose-50">Vault</a>
                <a href="{{ route('coach.chat') }}" class="rounded-2xl bg-white p-4 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-violet-50">Coach</a>
            </div>

            @if($shopOpen)
                <div class="fixed inset-0 z-40 bg-slate-900/35" wire:click="toggleShop"></div>
                <aside class="fixed inset-y-0 right-0 z-50 w-full max-w-md overflow-y-auto border-l border-slate-200 bg-white p-5 shadow-2xl">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-xl font-semibold text-slate-900">Build Menu</h2>
                        <button wire:click="toggleShop" class="rounded-lg bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-700">Close</button>
                    </div>

                    <div class="space-y-3">
                        <input wire:model.live.debounce.250ms="shopSearch" type="text"
                            class="w-full rounded-xl border-slate-300 text-sm focus:border-slate-500 focus:ring-slate-500"
                            placeholder="Search item name">

                        <select wire:model.live="shopCategory"
                            class="w-full rounded-xl border-slate-300 text-sm focus:border-slate-500 focus:ring-slate-500">
                            @foreach($categories as $category)
                                <option value="{{ $category }}">{{ ucfirst($category) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mt-4 space-y-3">
                        @foreach($filteredCatalog as $itemKey => $definition)
                            @php
                                $itemState = $items[$itemKey] ?? null;
                                $level = $itemState['level'] ?? 0;
                                $nextCost = $this->nextCostFor($itemKey);
                                $locked = $this->isLocked($itemKey);
                            @endphp
                            <div class="rounded-2xl border border-slate-200 p-4 {{ $locked ? 'bg-slate-50' : 'bg-white' }}">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900">{{ $definition['name'] }}</p>
                                        <p class="text-xs text-slate-500">{{ ucfirst($definition['category']) }} | {{ ucfirst($definition['rarity']) }}</p>
                                        <p class="mt-2 text-xs text-slate-600">{{ $this->unlockTextFor($itemKey) }}</p>
                                        <p class="text-xs text-slate-500">Current level: {{ $level }}/{{ $definition['max_level'] }}</p>
                                    </div>
                                    @if($nextCost)
                                        <div class="rounded-lg bg-amber-50 px-2 py-1 text-xs font-semibold text-amber-700">
                                            {{ $nextCost['love_seeds'] ?? 0 }} seeds
                                        </div>
                                    @else
                                        <div class="rounded-lg bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-700">
                                            Max
                                        </div>
                                    @endif
                                </div>

                                <button wire:click="openUpgradeModal('{{ $itemKey }}')" @disabled($locked)
                                    class="mt-3 w-full rounded-xl px-3 py-2 text-sm font-semibold transition {{ $locked ? 'cursor-not-allowed bg-slate-200 text-slate-400' : 'bg-slate-900 text-white hover:bg-slate-700' }}">
                                    @if($locked)
                                        Locked
                                    @elseif($level === 0)
                                        Build
                                    @else
                                        Upgrade
                                    @endif
                                </button>
                            </div>
                        @endforeach
                    </div>
                </aside>
            @endif

            @if($selectedItemKey && $selectedItemData)
                @php
                    $selectedLevel = $items[$selectedItemKey]['level'] ?? 0;
                    $nextLevel = $selectedLevel + 1;
                    $selectedCost = $this->nextCostFor($selectedItemKey);
                    $currentVisual = $selectedLevel > 0 ? ($selectedItemData['visuals'][$selectedLevel] ?? 'none') : 'none';
                    $nextVisual = $selectedItemData['visuals'][$nextLevel] ?? 'max';
                    $locked = $this->isLocked($selectedItemKey);
                @endphp
                <div class="fixed inset-0 z-[60] bg-slate-900/45"></div>
                <div class="fixed inset-0 z-[61] flex items-center justify-center p-4">
                    <div class="w-full max-w-lg rounded-3xl border border-slate-200 bg-white p-6 shadow-2xl">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="text-xl font-semibold text-slate-900">{{ $selectedItemData['name'] }}</h3>
                            <button wire:click="closeUpgradeModal" class="rounded-lg bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-700">Close</button>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                                <p class="text-xs font-semibold uppercase text-slate-500">Current</p>
                                <p class="mt-1 text-sm font-semibold text-slate-900">Lv {{ $selectedLevel }}</p>
                                <p class="text-xs text-slate-600">{{ $currentVisual }}</p>
                            </div>
                            <div class="rounded-xl border border-slate-200 bg-emerald-50 p-3">
                                <p class="text-xs font-semibold uppercase text-emerald-700">After Upgrade</p>
                                <p class="mt-1 text-sm font-semibold text-slate-900">Lv {{ $nextLevel }}</p>
                                <p class="text-xs text-slate-600">{{ $nextVisual }}</p>
                            </div>
                        </div>

                        @if($selectedCost)
                            <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800">
                                Cost: {{ $selectedCost['love_seeds'] ?? 0 }} seeds {{ isset($selectedCost['xp']) ? '| '.$selectedCost['xp'].' XP' : '' }}
                            </div>
                        @else
                            <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-700">
                                This item is already maxed.
                            </div>
                        @endif

                        @error('upgrade')
                            <p class="mt-3 text-sm font-medium text-rose-600">{{ $message }}</p>
                        @enderror

                        <div class="mt-5 flex items-center justify-end gap-2">
                            @if($selectedLevel > 0)
                                <button wire:click="startPlacementMode('{{ $selectedItemKey }}')"
                                    class="rounded-xl bg-sky-100 px-4 py-2 text-sm font-semibold text-sky-700 hover:bg-sky-200">
                                    Move Item
                                </button>
                            @endif
                            <button wire:click="closeUpgradeModal"
                                class="rounded-xl bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700">
                                Cancel
                            </button>
                            <button wire:click="upgradeSelectedItem" @disabled($locked || ! $selectedCost)
                                class="rounded-xl px-4 py-2 text-sm font-semibold text-white transition {{ ($locked || ! $selectedCost) ? 'cursor-not-allowed bg-slate-300' : 'bg-slate-900 hover:bg-slate-700' }}">
                                Confirm Upgrade
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            @if($memoryFrameHighlight)
                <div class="mt-6 rounded-3xl border border-slate-200 bg-white/95 p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Memory Frame</p>
                    <div class="mt-2 flex items-center gap-4">
                        @if($memoryFrameHighlight['thumbnail_url'])
                            <img src="{{ $memoryFrameHighlight['thumbnail_url'] }}" alt="Memory frame thumbnail"
                                class="h-16 w-16 rounded-xl object-cover">
                        @else
                            <div class="flex h-16 w-16 items-center justify-center rounded-xl bg-slate-100 text-xs font-semibold text-slate-500">
                                {{ strtoupper(substr($memoryFrameHighlight['type'], 0, 4)) }}
                            </div>
                        @endif
                        <div>
                            <p class="text-sm font-semibold text-slate-900">{{ $memoryFrameHighlight['title'] }}</p>
                            <a href="{{ route('vault.memory', ['memoryId' => $memoryFrameHighlight['id']]) }}"
                                class="text-xs font-semibold text-sky-700 hover:text-sky-600">
                                Open in vault
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            @if($recentXpEvents && $recentXpEvents->count() > 0)
                <div class="mt-6 rounded-3xl border border-slate-200 bg-white/90 p-6 shadow-sm">
                    <h2 class="mb-3 text-lg font-semibold text-slate-900">Recent Activity</h2>
                    <div class="space-y-2">
                        @foreach($recentXpEvents as $event)
                            <div class="flex items-center justify-between rounded-xl bg-slate-50 px-3 py-2">
                                <div>
                                    <p class="text-sm font-semibold capitalize text-slate-800">{{ str_replace('_', ' ', $event->type) }}</p>
                                    <p class="text-xs text-slate-500">{{ $event->created_at->diffForHumans() }}</p>
                                </div>
                                <p class="text-sm font-semibold text-emerald-600">+{{ $event->xp_amount }} XP</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @else
            <div class="mx-auto mt-10 max-w-2xl rounded-3xl border border-slate-200 bg-white p-8 text-center shadow-sm">
                <h2 class="text-2xl font-semibold text-slate-900">Create Your Shared World</h2>
                <p class="mt-2 text-slate-600">Link with your partner to unlock your home world and missions.</p>
                <a href="/couple/create-or-join"
                    class="mt-6 inline-flex rounded-xl bg-slate-900 px-5 py-3 font-semibold text-white transition hover:bg-slate-700">
                    Create or Join Couple
                </a>
            </div>
        @endif
    </div>
</div>
