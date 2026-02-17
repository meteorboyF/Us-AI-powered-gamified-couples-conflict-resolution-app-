@props(['title', 'why', 'price', 'tip'])

<x-rpg-panel class="border-sky shadow-[4px_4px_0_#78C2E8] hover:translate-x-1 hover:-translate-y-1 transition-transform">
    <div class="flex justify-between items-start mb-2">
        <h4 class="font-pixel text-2xl text-rose leading-none">{{ $title }}</h4>
        <span class="bg-sky/20 text-sky font-pixel text-xs px-2 py-1 border border-sky/30 uppercase">{{ $price }}</span>
    </div>
    
    <div class="mb-4">
        <span class="font-pixel text-xs text-toast/60 uppercase block">Why it fits:</span>
        <p class="text-sm italic text-cocoa">"{{ $why }}"</p>
    </div>

    <div class="bg-white/40 p-2 border-l-4 border-rose">
        <span class="font-pixel text-[10px] text-rose uppercase block font-bold">Personalization Tip:</span>
        <p class="text-xs text-cocoa leading-tight">{{ $tip }}</p>
    </div>
</x-rpg-panel>