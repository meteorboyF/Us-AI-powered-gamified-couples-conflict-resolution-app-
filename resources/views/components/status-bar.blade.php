@props(['currentCoupleId' => null])

<div class="fixed top-0 left-0 right-0 z-50 px-4 pt-4">
    <div class="max-w-4xl mx-auto grid grid-cols-3 items-center bg-slate-900/90 border border-slate-700 backdrop-blur-md px-4 py-2 rounded-md shadow">
        <div class="flex gap-4 items-center text-xs uppercase tracking-wide">
            <div>
                <p class="text-slate-300">XP</p>
                <p class="text-white text-lg font-semibold">1,240</p>
            </div>
            <div class="h-8 w-px bg-slate-600"></div>
            <div>
                <p class="text-slate-300">Current Couple</p>
                <p class="text-white text-lg font-semibold">{{ $currentCoupleId ?: 'None' }}</p>
            </div>
        </div>

        <div class="flex justify-center">
            <div class="flex gap-1">
                @for ($i = 0; $i < 5; $i++)
                    <span class="w-3 h-3 border border-black/30 {{ $i < 3 ? 'bg-pink-500' : 'bg-slate-600' }}"></span>
                @endfor
            </div>
        </div>

        <div class="flex items-center justify-end gap-4">
            <a href="/app" class="text-xs text-slate-200 hover:text-white">App Hub</a>
            <a href="/profile" class="w-9 h-9 bg-amber-200 text-slate-900 rounded border border-amber-300 flex items-center justify-center">??</a>
        </div>
    </div>
</div>
