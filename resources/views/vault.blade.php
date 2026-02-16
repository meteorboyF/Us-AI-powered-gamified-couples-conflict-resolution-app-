<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Us — Memory Vault</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-navy min-h-screen p-4 pb-24">

    <!-- Header (PDF Page 6: Memory Safe) -->
    <header class="max-w-2xl mx-auto mb-8 flex items-center justify-between">
        <a href="/dashboard" class="text-sand font-pixel text-xl">← HOME</a>
        <div class="text-center">
            <h1 class="text-3xl text-white font-pixel uppercase tracking-widest">The Vault</h1>
            <p class="text-moonlight font-pixel text-xs">Our Journey Together</p>
        </div>
        <button class="bg-cocoa border-2 border-toast p-2 text-xl">🔐</button>
    </header>

    <main class="max-w-2xl mx-auto">
        
        <!-- Timeline Section (PDF Page 7) -->
        <div class="mb-10 relative">
            <div class="absolute left-1/2 -translate-x-1/2 top-0 bottom-0 w-1 bg-toast/30"></div>
            
            <div class="relative flex justify-center mb-4">
                <div class="bg-rose text-white font-pixel px-4 py-1 rounded-full text-sm z-10 border-2 border-white">
                    Our First Date: Jan 12
                </div>
            </div>
        </div>

        <!-- Memory Grid (PDF Page 18: Gallery View) -->
        <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
            <x-memory-card date="DEC 2025">
                Beach Walk
            </x-memory-card>

            <x-memory-card type="note" date="JAN 2026">
                That funny joke...
            </x-memory-card>

            <x-memory-card type="audio" date="FEB 2026">
                Voice Note #1
            </x-memory-card>

            <x-memory-card locked="true" date="LOCKED">
                Private Note
            </x-memory-card>

            <x-memory-card date="MAR 2026">
                Dinner Night
            </x-memory-card>

            <!-- Add Memory Button -->
            <div class="border-4 border-dashed border-sand/30 flex flex-col items-center justify-center p-4 aspect-square hover:bg-white/5 transition-colors cursor-pointer group">
                <span class="text-4xl text-sand group-hover:scale-110 transition-transform">+</span>
                <span class="font-pixel text-sand text-xs mt-2 uppercase">Add Memory</span>
            </div>
        </div>

    </main>

    <!-- Comfort Mode Prompt (PDF Page 7: Vibe-triggered suggestion) -->
    <div class="fixed bottom-6 left-1/2 -translate-x-1/2 w-full max-w-sm px-4">
        <div class="bg-parchment border-4 border-sky p-3 flex items-center gap-4 shadow-xl">
            <span class="text-2xl">✨</span>
            <p class="text-sm leading-tight text-cocoa">
                <strong class="font-pixel text-sky block uppercase text-xs">Comfort Mode</strong>
                Remember your walk on the beach? It might cheer you up.
            </p>
        </div>
    </div>

</body>
</html>