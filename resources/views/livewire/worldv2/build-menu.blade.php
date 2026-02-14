<div>
@if ($open)
    <div class="fixed inset-0 z-40 bg-[#0F1B2D]/45" wire:click="$parent.closeBuildMenu"></div>
    <aside class="fixed inset-y-0 right-0 z-50 w-full max-w-md overflow-y-auto border-l-[3px] border-[#6B3F2A] bg-[#F6E7C8] p-4 shadow-[-8px_0_0_#2B1B12]">
        <style>
            .worldv2-card {
                border: 2px solid #6B3F2A;
                background: linear-gradient(180deg, #F6E7C8, #E9D6AE);
                box-shadow: 0 3px 0 #2B1B12;
            }

            .worldv2-chip {
                border: 2px solid #9B2C6B;
                background: #F6E7C8;
                color: #9B2C6B;
            }

            .worldv2-action {
                border: 2px solid #6B3F2A;
                background: #E9D6AE;
                color: #2B1B12;
                box-shadow: 0 3px 0 #2B1B12;
            }

            .worldv2-action:disabled {
                opacity: 0.5;
                cursor: not-allowed;
            }
        </style>

        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-xl font-semibold text-[#2B1B12]">Build Menu</h2>
            <button wire:click="$parent.closeBuildMenu" class="worldv2-action rounded-md px-2 py-1 text-sm font-semibold">Close</button>
        </div>

        @php
            $grouped = collect($items)->groupBy('category');
            $orderedCategories = ['core', 'decor', 'utility', 'comfort'];
        @endphp

        <div class="space-y-4">
            @foreach ($orderedCategories as $category)
                @if (! $grouped->has($category))
                    @continue
                @endif

                <section>
                    <h3 class="mb-2 text-sm font-semibold uppercase tracking-wide text-[#6B3F2A]">{{ $category }}</h3>
                    <div class="space-y-3">
                        @foreach ($grouped->get($category) as $item)
                            <article class="worldv2-card rounded-lg p-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-semibold text-[#2B1B12]">{{ $item['name'] }}</p>
                                        <p class="text-xs text-[#6B3F2A]">Lv {{ $item['level'] }} / {{ $item['max_level'] }}</p>
                                    </div>
                                    @if ($item['locked'])
                                        <span class="worldv2-chip rounded-full px-2 py-1 text-[11px] font-semibold">Locked</span>
                                    @elseif (! $item['next_cost'])
                                        <span class="rounded-full border-2 border-[#58B368] bg-[#F6E7C8] px-2 py-1 text-[11px] font-semibold text-[#58B368]">Max</span>
                                    @else
                                        <span class="rounded-full border-2 border-[#6B3F2A] bg-[#F2C14E] px-2 py-1 text-[11px] font-semibold text-[#2B1B12]">{{ $item['next_cost']['love_seeds'] ?? 0 }} Seeds</span>
                                    @endif
                                </div>

                                <p class="mt-2 text-xs text-[#6B3F2A]">{{ $item['unlock_text'] }}</p>

                                <button
                                    wire:click="$parent.openUpgradeModal('{{ $item['item_key'] }}')"
                                    class="worldv2-action mt-3 w-full rounded-md px-3 py-2 text-sm font-semibold"
                                    @disabled($item['locked'] && $item['level'] === 0)>
                                    @if ($item['level'] === 0)
                                        Build
                                    @elseif ($item['next_cost'])
                                        Upgrade
                                    @else
                                        View
                                    @endif
                                </button>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    </aside>
@endif
</div>
