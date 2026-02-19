@php
    $sceneItems = $world['items'] ?? [];
    $vibe = $world['vibe'] ?? 'neutral';
    $xp = $world['xp'] ?? 0;
@endphp

<div class="relative z-10 flex flex-col items-center" id="world-scene-root" data-vibe="{{ $vibe }}">
    <div class="h-[35vh]"></div>

    <div class="w-full bg-emerald-800 border-t-4 border-emerald-700 min-h-[55vh] pb-64 shadow-[0_-20px_50px_rgba(0,0,0,0.3)]">
        <div class="max-w-5xl mx-auto px-8 pt-12 space-y-8">
            <div class="flex flex-wrap items-center justify-between gap-3 bg-white/90 border border-slate-200 rounded p-4 text-slate-900">
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-600">Home Base</p>
                    <p class="font-semibold">Vibe: <span id="dashboard-vibe">{{ $vibe }}</span></p>
                </div>
                <div class="text-sm text-slate-700">XP: <span id="dashboard-xp">{{ $xp }}</span></div>
                <form id="dashboard-vibe-form" class="flex items-center gap-2">
                    @csrf
                    <label for="dashboard-vibe-input" class="text-xs uppercase text-slate-600">Set vibe</label>
                    <select id="dashboard-vibe-input" name="vibe" class="text-sm rounded border-slate-300">
                        @foreach (['neutral', 'warm', 'playful', 'tense', 'repair'] as $option)
                            <option value="{{ $option }}" @selected($vibe === $option)>{{ ucfirst($option) }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="px-3 py-1 text-xs bg-slate-700 text-white rounded">Update</button>
                </form>
            </div>

            <div class="grid grid-cols-4 gap-6">
                <div class="relative h-32 flex flex-col items-center justify-end">
                    <div class="text-7xl drop-shadow-2xl animate-bounce">??</div>
                    <div class="w-16 h-4 bg-black/20 rounded-full blur-sm -mt-2"></div>
                </div>

                @for ($i = 0; $i < 3; $i++)
                    <button
                        type="button"
                        @click="showBuildMenu = true"
                        class="w-24 h-24 border-2 border-dashed border-white/25 rounded-xl mt-8 flex items-center justify-center text-white/60 text-4xl hover:bg-white/10 hover:border-white/40 transition-all"
                    >
                        +
                    </button>
                @endfor
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4" id="dashboard-world-items">
                @foreach ($sceneItems as $item)
                    <div class="bg-white/90 border border-slate-200 rounded p-4 text-slate-900">
                        <p class="font-semibold">{{ $item['title'] }}</p>
                        <p class="text-xs text-slate-500">{{ $item['key'] }}</p>
                        <p class="text-sm mt-2">{{ $item['description'] ?: 'No description yet.' }}</p>
                        <div class="mt-3">
                            @if ($item['unlocked'])
                                <span class="inline-flex px-2 py-1 rounded bg-emerald-100 text-emerald-700 text-xs font-medium">Unlocked</span>
                            @else
                                <button
                                    type="button"
                                    class="dashboard-unlock-btn px-3 py-1 text-xs bg-amber-200 text-slate-900 rounded border border-amber-300"
                                    data-key="{{ $item['key'] }}"
                                >
                                    Unlock
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
