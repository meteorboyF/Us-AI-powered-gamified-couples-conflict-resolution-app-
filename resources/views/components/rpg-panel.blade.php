<div {{ $attributes->merge(['class' => 'relative bg-parchment border-4 border-toast rounded-sm shadow-[6px_6px_0px_0px_rgba(43,27,18,1)]']) }}>
    <!-- Inner Bevel (PDF Page 13: Soft lighting/textures) -->
    <div class="absolute inset-0 border-2 border-white/30 pointer-events-none"></div>
    
    <!-- Content -->
    <div class="p-6 relative z-10 text-cocoa">
        {{ $slot }}
    </div>
</div>