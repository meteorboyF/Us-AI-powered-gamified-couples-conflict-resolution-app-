<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Us — Gift Shop</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-navy min-h-screen p-4 pb-24">

    <!-- Header -->
    <header class="max-w-2xl mx-auto mb-8 flex items-center justify-between">
        <a href="/dashboard" class="text-sand font-pixel text-xl">← WORLD</a>
        <div class="text-center">
            <h1 class="text-3xl text-white font-pixel uppercase tracking-widest">Gift Finder</h1>
            <p class="text-moonlight font-pixel text-xs">AI-Powered Surprises</p>
        </div>
        <div class="text-3xl">🎁</div>
    </header>

    <main class="max-w-2xl mx-auto space-y-10">
        
        <!-- INPUT FORM (PDF Page 9: Form Inputs) -->
        <section>
            <x-rpg-panel>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Occasion -->
                    <div>
                        <label class="font-pixel text-rose uppercase text-sm block mb-1">The Occasion</label>
                        <select class="w-full bg-white border-4 border-toast p-2 font-pixel text-lg focus:outline-none">
                            <option>Anniversary</option>
                            <option>Just Because</option>
                            <option>Sorry / Repair</option>
                            <option>Birthday</option>
                        </select>
                    </div>

                    <!-- Budget -->
                    <div>
                        <label class="font-pixel text-rose uppercase text-sm block mb-1">Budget Range</label>
                        <select class="w-full bg-white border-4 border-toast p-2 font-pixel text-lg focus:outline-none">
                            <option>$0 (Handmade)</option>
                            <option>$1 - $50</option>
                            <option>$50 - $200</option>
                            <option>$200+</option>
                        </select>
                    </div>

                    <!-- Personality -->
                    <div class="md:col-span-2">
                        <label class="font-pixel text-rose uppercase text-sm block mb-1">Partner Interests & Vibe</label>
                        <textarea 
                            class="w-full bg-white border-4 border-toast p-3 font-sans text-sm focus:outline-none h-24"
                            placeholder="e.g. Loves cozy games, enjoys plants, prefers quiet nights over parties..."></textarea>
                    </div>
                </div>

                <div class="mt-6">
                    <x-pixel-button class="w-full">✨ Summon Gift Ideas ✨</x-pixel-button>
                </div>
            </x-rpg-panel>
        </section>

        <!-- RESULTS (Output Format Page 9) -->
        <section class="space-y-6">
            <h2 class="font-pixel text-2xl text-moonlight uppercase tracking-tighter">AI Suggestions</h2>
            
            <div class="grid grid-cols-1 gap-4">
                <x-gift-idea-card 
                    title="Hand-Painted Flower Pot"
                    price="$10-20"
                    why="Matches their love for plants and your shared garden progress."
                    tip="Paint a small pixel-art version of your in-game house on the side of the pot."
                />

                <x-gift-idea-card 
                    title="Custom Pixel-Art Portrait"
                    price="$30-50"
                    why="A romantic way to bring your 'Us' avatars into the real world."
                    tip="Frame it in a chunky wooden frame to match the game UI look."
                />
            </div>
        </section>

    </main>

</body>
</html>