<div class="space-y-6">
    <div class="rounded-3xl border border-white/70 bg-white/85 p-6 shadow-sm backdrop-blur">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Generate Suggestions</h2>
                <p class="mt-1 text-sm text-gray-600">Creates 10-12 cards using Gemini, then falls back safely if needed.</p>
            </div>
            <button wire:click="generate"
                class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">
                Generate
            </button>
        </div>

        @if ($statusMessage)
            <div class="mt-4 rounded-xl px-4 py-3 text-sm {{ $source === 'fallback' ? 'border border-amber-200 bg-amber-50 text-amber-800' : 'border border-emerald-200 bg-emerald-50 text-emerald-700' }}">
                {{ $statusMessage }}
            </div>
        @endif
    </div>

    @if (!empty($cards))
        <div class="grid gap-4 md:grid-cols-2">
            @foreach($cards as $card)
                <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
                    <div class="mb-2 flex items-center justify-between gap-2">
                        <h3 class="font-semibold text-gray-900">{{ $card['title'] }}</h3>
                        <span class="rounded-full bg-orange-100 px-2 py-1 text-xs font-semibold uppercase tracking-wide text-orange-700">
                            {{ $card['category'] }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-700">{{ $card['description'] }}</p>
                    <p class="mt-2 text-xs font-medium text-gray-600">Why it fits: {{ $card['why_it_fits'] }}</p>
                    @if (!empty($card['estimated_cost']) || !empty($card['time_required']))
                        <p class="mt-2 text-xs text-gray-500">
                            @if (!empty($card['estimated_cost'])) Cost: {{ $card['estimated_cost'] }} @endif
                            @if (!empty($card['estimated_cost']) && !empty($card['time_required'])) • @endif
                            @if (!empty($card['time_required'])) Time: {{ $card['time_required'] }} @endif
                        </p>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    <div class="rounded-3xl border border-white/70 bg-white/85 p-6 shadow-sm backdrop-blur">
        <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-700">Recent Generations</h3>
        <div class="mt-3 space-y-2">
            @forelse($history as $entry)
                <div class="rounded-xl border border-gray-100 bg-gray-50 px-3 py-2 text-sm text-gray-700">
                    <span class="font-semibold uppercase">{{ $entry['source'] }}</span>
                    • {{ \Illuminate\Support\Carbon::parse($entry['created_at'])->format('M d, Y H:i') }}
                </div>
            @empty
                <p class="text-sm text-gray-500">No suggestions generated yet.</p>
            @endforelse
        </div>
    </div>
</div>
