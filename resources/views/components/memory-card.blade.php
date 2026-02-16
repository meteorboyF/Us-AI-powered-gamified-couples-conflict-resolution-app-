@props(['type' => 'photo', 'date' => 'Oct 2025', 'locked' => false])

<div {{ $attributes->merge(['class' => 'relative bg-white p-2 shadow-md rotate-1 hover:rotate-0 transition-transform cursor-pointer group']) }}>
    <!-- Tape detail (PDF Page 13: Cozy/warm textures) -->
    <div class="absolute -top-3 left-1/2 -translate-x-1/2 w-10 h-6 bg-sand/80 border border-toast/20 rotate-2"></div>

    <div class="bg-stone-200 aspect-square mb-2 overflow-hidden relative">
        @if($locked)
            <div class="absolute inset-0 bg-cocoa/90 flex flex-col items-center justify-center text-white p-4 text-center">
                <span class="text-3xl mb-1">🔒</span>
                <span class="font-pixel text-xs">Locked for Both</span>
            </div>
        @else
            <!-- Placeholder for image/content -->
            <div class="w-full h-full flex items-center justify-center text-4xl group-hover:scale-110 transition-transform">
                {{ $type === 'photo' ? '🖼️' : ($type === 'note' ? '📝' : '🎙️') }}
            </div>
        @endif
    </div>

    <div class="px-1">
        <p class="font-pixel text-cocoa text-sm leading-none">{{ $slot }}</p>
        <p class="font-pixel text-[10px] text-toast/60 mt-1 uppercase">{{ $date }}</p>
    </div>
</div>