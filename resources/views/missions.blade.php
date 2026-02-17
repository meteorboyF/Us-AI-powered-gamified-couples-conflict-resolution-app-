<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Us — Daily Missions</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-navy min-h-screen p-4 pb-24 text-white">

    <header class="max-w-xl mx-auto mb-8 flex items-center justify-between">
        <a href="/dashboard" class="text-sand font-pixel text-xl">← WORLD</a>
        <h1 class="text-3xl font-pixel uppercase">Missions</h1>
        <div class="flex items-center gap-1">
            <span class="text-gold font-pixel text-xl">1240</span>
            <span class="text-xl">⭐</span>
        </div>
    </header>

    <main class="max-w-xl mx-auto space-y-8">
        
        <!-- DAILY CHECK-IN (PDF Page 9: Mood Slider) -->
        <section>
            <h2 class="font-pixel text-2xl text-moonlight mb-4 uppercase tracking-tighter">Daily Check-in</h2>
            <x-rpg-panel>
                <div class="space-y-6">
                    <p class="text-center font-pixel text-2xl">How's your heart today?</p>
                    
                    <!-- Stylized Mood Slider -->
                    <div class="px-4">
                        <input type="range" min="1" max="5" step="1" 
                               class="w-full h-4 bg-toast rounded-lg appearance-none cursor-pointer accent-rose">
                        <div class="flex justify-between mt-2 font-pixel text-xs text-toast uppercase">
                            <span>Tired</span>
                            <span>Neutral</span>
                            <span>Happy!</span>
                        </div>
                    </div>

                    <x-pixel-button class="w-full">Submit Check-in</x-pixel-button>
                </div>
            </x-rpg-panel>
        </section>

        <!-- MISSION BOARD (PDF Page 8: Mission Types) -->
        <section>
            <h2 class="font-pixel text-2xl text-moonlight mb-4 uppercase tracking-tighter">Daily Missions</h2>
            <div class="space-y-4">
                <x-mission-card 
                    icon="💌" 
                    title="Send 1 Appreciation Note" 
                    reward="50 XP" />
                
                <x-mission-card 
                    icon="🎙️" 
                    title="Send a 2-minute voice note" 
                    reward="30 XP" />
                
                <x-mission-card 
                    completed="true"
                    icon="❓" 
                    title="Ask 1 curious question" 
                    reward="20 XP" />
                
                <x-mission-card 
                    icon="🕯️" 
                    title="Plan a micro-date" 
                    reward="100 XP + 5 Seeds" />
            </div>
        </section>

    </main>

    <!-- Navigation Prompt -->
    <div class="fixed bottom-6 left-1/2 -translate-x-1/2 text-center w-full">
        <p class="font-pixel text-moonlight animate-pulse uppercase text-xs">Missions reset in 14h 20m</p>
    </div>

</body>
</html>