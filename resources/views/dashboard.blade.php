<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Us — Shared World</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-navy h-screen overflow-hidden">

    <x-status-bar />

    <!-- GAME WORLD (PDF Page 17: Layered Scene) -->
    <main class="relative h-full w-full flex items-center justify-center">

        <!-- Layer 1: Sky (Background) -->
        <div class="absolute inset-0 bg-gradient-to-b from-navy to-[#1a2a44]"></div>

        <!-- Layer 2: Distant Trees (Placeholder) -->
        <div class="absolute bottom-40 w-full flex justify-around opacity-30">
            <div class="w-32 h-64 bg-emerald-900 rounded-t-full"></div>
            <div class="w-40 h-80 bg-emerald-950 rounded-t-full"></div>
            <div class="w-32 h-64 bg-emerald-900 rounded-t-full"></div>
        </div>

        <!-- Layer 3: Ground (Foreground) -->
        <div class="absolute bottom-0 w-full h-48 bg-leaf border-t-8 border-[#3d8c4d]">
            <!-- Grid for item placement (PDF Page 17: Slot highlights) -->
            <div class="grid grid-cols-4 h-full max-w-4xl mx-auto gap-4 p-8">
                <div
                    class="border-2 border-dashed border-white/20 rounded-lg hover:bg-white/10 transition-colors cursor-pointer flex items-center justify-center text-white/20 font-pixel text-4xl">
                    +</div>
                <div class="border-2 border-dashed border-white/20 rounded-lg flex items-center justify-center">
                    <span class="text-4xl animate-bounce">🏡</span> <!-- Home placeholder -->
                </div>
                <div
                    class="border-2 border-dashed border-white/20 rounded-lg hover:bg-white/10 transition-colors cursor-pointer flex items-center justify-center text-white/20 font-pixel text-4xl">
                    +</div>
                <div
                    class="border-2 border-dashed border-white/20 rounded-lg hover:bg-white/10 transition-colors cursor-pointer flex items-center justify-center text-white/20 font-pixel text-4xl">
                    +</div>
            </div>
        </div>

        <!-- Layer 4: Floating UI Prompts -->
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2">
            <div class="bg-parchment/90 border-2 border-toast px-4 py-2 rounded shadow-lg animate-bounce">
                <p class="font-pixel text-cocoa">Tap to plant Love Seeds</p>
            </div>
        </div>

    </main>

    <!-- BOTTOM MENU (PDF Page 17: Build Menu Drawer) -->
    <nav class="fixed bottom-0 left-0 right-0 p-4">
        <div
            class="max-w-md mx-auto bg-parchment border-4 border-toast flex justify-around p-2 shadow-[0_-6px_0_rgba(0,0,0,0.2)]">
            <button class="flex flex-col items-center p-2 hover:bg-sand rounded transition-colors group">
                <span class="text-2xl group-active:scale-90 transition-transform">🛠️</span>
                <span class="font-pixel text-xs">BUILD</span>
            </button>
            <a href="/chat"
                class="flex flex-col items-center p-2 hover:bg-sand rounded transition-colors group cursor-pointer">
                <span class="text-2xl group-active:scale-90 transition-transform">💬</span>
                <span class="font-pixel text-xs">CHAT</span>
            </a>
            <button class="flex flex-col items-center p-2 hover:bg-sand rounded transition-colors group">
                <span class="text-2xl group-active:scale-90 transition-transform">🔐</span>
                <span class="font-pixel text-xs">VAULT</span>
            </button>
            <button class="flex flex-col items-center p-2 hover:bg-sand rounded transition-colors group">
                <span class="text-2xl group-active:scale-90 transition-transform">🤖</span>
                <span class="font-pixel text-xs">AI COACH</span>
            </button>
        </div>
    </nav>

</body>

</html>