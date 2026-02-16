<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Us — AI Coach</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-navy min-h-screen p-4 pb-24">

    <!-- Header (PDF Page 7: Communication Coach) -->
    <header class="max-w-xl mx-auto mb-6 flex items-center justify-between">
        <a href="/dashboard" class="text-sand font-pixel text-xl">← EXIT</a>
        <h1 class="text-3xl text-white font-pixel uppercase tracking-widest">AI Coach</h1>
        <div class="text-2xl animate-bounce">🤖</div>
    </header>

    <!-- Mode Switcher (PDF Page 19) -->
    <div class="max-w-xl mx-auto mb-6 grid grid-cols-3 gap-2">
        <button class="bg-rose text-white border-b-4 border-berry p-2 font-pixel text-sm uppercase active:translate-y-1 active:border-b-0">Vent</button>
        <button class="bg-sand text-cocoa border-b-4 border-toast p-2 font-pixel text-sm uppercase opacity-50">Bridge</button>
        <button class="bg-sand text-cocoa border-b-4 border-toast p-2 font-pixel text-sm uppercase opacity-50">Repair</button>
    </div>

    <main class="max-w-xl mx-auto space-y-6">
        
        <!-- VENT MODE UI (PDF Page 7: Private journaling) -->
        <x-rpg-panel>
            <div class="space-y-4">
                <div class="flex items-start gap-3">
                    <span class="text-3xl">📝</span>
                    <div>
                        <h3 class="font-pixel text-2xl text-berry">Vent Mode</h3>
                        <p class="text-sm">Speak your mind freely. This is private and stays between you and the coach.</p>
                    </div>
                </div>

                <textarea 
                    class="w-full h-40 bg-white/50 border-2 border-toast p-4 font-sans text-cocoa focus:outline-none focus:bg-white transition-colors"
                    placeholder="How are you feeling right now? What's on your mind?"></textarea>
                
                <x-pixel-button class="w-full">Analyze Feelings</x-pixel-button>
            </div>
        </x-rpg-panel>

        <!-- REPAIR MODE PREVIEW (PDF Page 19: Step list UI) -->
        <div class="opacity-60 grayscale pointer-events-none">
            <x-rpg-panel>
                <h3 class="font-pixel text-2xl text-leaf mb-4 italic">Example Repair Plan:</h3>
                <ul class="space-y-3 font-pixel text-xl">
                    <li class="flex items-center gap-2">
                        <div class="w-6 h-6 border-2 border-toast"></div>
                        Take a 10-minute cool-off walk
                    </li>
                    <li class="flex items-center gap-2">
                        <div class="w-6 h-6 border-2 border-toast"></div>
                        Use an "I feel" statement
                    </li>
                </ul>
            </x-rpg-panel>
        </div>

    </main>

    <!-- Navigation Prompt (PDF Page 13: Floating over scene) -->
    <div class="fixed bottom-6 left-1/2 -translate-x-1/2 w-full max-w-xs text-center">
        <p class="text-moonlight font-pixel text-lg drop-shadow-md">Coach is listening...</p>
    </div>

</body>
</html>