@props(['title', 'reward' => '10 XP', 'icon' => '⭐', 'completed' => false])

<div class="relative bg-parchment border-4 {{ $completed ? 'border-leaf opacity-75' : 'border-toast' }} p-4 shadow-[4px_4px_0_rgba(0,0,0,0.1)] transition-all">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <span class="text-3xl">{{ $icon }}</span>
            <div>
                <h4 class="font-pixel text-xl {{ $completed ? 'line-through text-leaf' : 'text-cocoa' }}">{{ $title }}</h4>
                <div class="flex gap-2 items-center">
                    <span class="bg-gold/20 text-gold font-pixel text-xs px-2 border border-gold/30">+{{ $reward }}</span>
                    @if($completed)
                        <span class="text-leaf font-pixel text-xs uppercase">Completed!</span>
                    @endif
                </div>
            </div>
        </div>
        
        <button class="w-8 h-8 border-4 {{ $completed ? 'bg-leaf border-leaf' : 'border-toast bg-white' }} flex items-center justify-center">
            @if($completed) <span class="text-white">✓</span> @endif
        </button>
    </div>
</div>