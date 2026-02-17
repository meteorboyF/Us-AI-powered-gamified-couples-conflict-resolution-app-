<div class="fixed top-0 left-0 right-0 z-50 px-4 pt-4">
    <div class="max-w-2xl mx-auto grid grid-cols-3 items-center bg-cocoa/90 border-4 border-toast backdrop-blur-md px-6 py-2 shadow-[0_4px_0_rgba(0,0,0,0.3)]">
        
        <!-- Left: Stats -->
        <div class="flex gap-6 items-center">
            <div class="flex flex-col">
                <span class="text-gold font-pixel text-[10px] uppercase leading-none mb-1">XP</span>
                <span class="text-white font-pixel text-2xl leading-none">1,240</span>
            </div>
            <div class="w-[2px] h-8 bg-toast/50"></div>
            <div class="flex flex-col">
                <span class="text-sky font-pixel text-[10px] uppercase leading-none mb-1">Seeds</span>
                <span class="text-white font-pixel text-2xl leading-none">42</span>
            </div>
        </div>

        <!-- Center: Vibe (Centered perfectly) -->
        <div class="flex flex-col items-center justify-center">
            <span class="text-rose font-pixel text-[10px] uppercase leading-none mb-2 tracking-widest">Vibe Meter</span>
            <div class="flex gap-1.5">
                @for ($i = 0; $i < 5; $i++)
                    <div class="w-3.5 h-3.5 {{ $i < 3 ? 'bg-rose shadow-[0_0_8px_rgba(232,90,155,0.6)]' : 'bg-toast' }} border border-black/20"></div>
                @endfor
            </div>
        </div>

        <!-- Right: Profile & Streak -->
        <div class="flex items-center justify-end gap-4">
            <div class="flex items-center gap-1">
                <span class="text-white font-pixel text-2xl">7</span>
                <span class="text-xl">🔥</span>
            </div>
            <a href="/profile" class="w-10 h-10 bg-sand border-2 border-white shadow-sm flex items-center justify-center text-xl hover:scale-110 active:scale-95 transition-transform">
                👤
            </a>
        </div>
    </div>
</div>