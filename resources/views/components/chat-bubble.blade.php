@props(['side' => 'left', 'sender' => 'Partner'])

<div class="flex w-full {{ $side === 'right' ? 'justify-end' : 'justify-start' }} mb-4">
    <div class="max-w-[80%]">
        <!-- Sender Name (Pixel style) -->
        <span class="block font-pixel text-xs uppercase mb-1 {{ $side === 'right' ? 'text-right text-rose' : 'text-left text-sky' }}">
            {{ $sender }}
        </span>

        <!-- Dialog Box (PDF Page 18: RPG dialog layout) -->
        <div class="relative bg-parchment border-4 {{ $side === 'right' ? 'border-rose shadow-[4px_4px_0_rgba(232,90,155,0.2)]' : 'border-toast shadow-[4px_4px_0_rgba(107,63,42,0.2)]' }} p-3">
            <!-- Little "tail" for the dialog box -->
            <div class="absolute -bottom-1 {{ $side === 'right' ? '-right-1' : '-left-1' }} w-4 h-4 bg-inherit border-inherit border-b-4 border-r-4 rotate-45"></div>
            
            <p class="relative z-10 text-cocoa leading-tight">
                {{ $slot }}
            </p>
        </div>
    </div>
</div>