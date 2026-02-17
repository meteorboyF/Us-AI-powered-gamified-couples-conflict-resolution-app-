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

        <!-- Layer 1: Sky with Vibe Overlay (PDF Page 4: Low vibe = gloom overlay) -->
        <div class="absolute inset-0 bg-gradient-to-b from-navy to-[#1a2a44] transition-all duration-1000">
            <!-- This overlay can be toggled via JS later to make things "Gloomier" -->
            <div class="absolute inset-0 bg-black/20 mix-blend-multiply"></div>
        </div>
        <!-- Layer 2: Distant Trees (Placeholder) -->
        <div class="absolute bottom-40 w-full flex justify-around opacity-30">
            <div class="w-32 h-64 bg-emerald-900 rounded-t-full"></div>
            <div class="w-40 h-80 bg-emerald-950 rounded-t-full"></div>
            <div class="w-32 h-64 bg-emerald-900 rounded-t-full"></div>
        </div>

        <!-- The Ground Layer -->
        <div class="absolute bottom-0 w-full h-64 bg-leaf border-t-8 border-[#3d8c4d]">
            <div class="grid grid-cols-4 h-full max-w-5xl mx-auto gap-8 px-12 pt-12">

                <!-- Empty Slot -->
                <div class="relative group cursor-pointer h-32">
                    <div
                        class="absolute inset-0 border-4 border-dashed border-white/20 group-hover:border-white/60 group-hover:bg-white/10 transition-all rounded-xl flex flex-col items-center justify-center">
                        <span class="text-white/20 group-hover:text-white/60 font-pixel text-4xl mb-1">+</span>
                        <span
                            class="opacity-0 group-hover:opacity-100 font-pixel text-white text-xs uppercase transition-opacity">Plant</span>
                    </div>
                </div>

                <!-- Occupied Slot (The House) -->
                <div class="relative h-32 flex flex-col items-center justify-end">
                    <div class="text-6xl filter drop-shadow-xl animate-bounce duration-[2000ms] relative z-10">🏡</div>
                    <div class="w-16 h-4 bg-black/20 rounded-full blur-sm -mt-2"></div> <!-- Shadow -->

                    <!-- Hover Label -->
                    <div
                        class="absolute -top-8 bg-cocoa text-white font-pixel text-[10px] px-2 py-1 opacity-0 group-hover:opacity-100 transition-opacity uppercase">
                        Lv. 1 Cottage
                    </div>
                </div>

                <!-- Another Empty Slot -->
                <div class="relative group cursor-pointer h-32">
                    <div
                        class="absolute inset-0 border-4 border-dashed border-white/20 group-hover:border-white/60 group-hover:bg-white/10 transition-all rounded-xl flex flex-col items-center justify-center">
                        <span class="text-white/20 group-hover:text-white/60 font-pixel text-4xl mb-1">+</span>
                    </div>
                </div>

                <!-- Another Empty Slot -->
                <div class="relative group cursor-pointer h-32">
                    <div
                        class="absolute inset-0 border-4 border-dashed border-white/20 group-hover:border-white/60 group-hover:bg-white/10 transition-all rounded-xl flex flex-col items-center justify-center">
                        <span class="text-white/20 group-hover:text-white/60 font-pixel text-4xl mb-1">+</span>
                    </div>
                </div>

            </div>
        </div>
        <!-- Layer 4: Floating UI Prompts -->
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2">
            <div class="bg-parchment/90 border-2 border-toast px-4 py-2 rounded shadow-lg animate-bounce">
                <p class="font-pixel text-cocoa">Tap to plant Love Seeds</p>
            </div>
        </div>

    </main>

    <nav class="fixed bottom-0 left-0 right-0 p-4">
        <div
            class="max-w-md mx-auto bg-parchment border-4 border-toast flex justify-around p-2 shadow-[0_-6px_0_rgba(0,0,0,0.2)]">
            <button class="flex flex-col items-center p-2 hover:bg-sand rounded transition-colors group">
                <span class="text-2xl group-active:scale-90 transition-transform">🛠️</span>
                <span class="font-pixel text-xs">BUILD</span>
            </button>

            <a href="/missions"
                class="flex flex-col items-center p-2 hover:bg-sand rounded transition-colors group cursor-pointer">
                <span class="text-2xl group-active:scale-90 transition-transform">📜</span>
                <span class="font-pixel text-xs">MISSIONS</span>
            </a>
            <a href="/chat"
                class="flex flex-col items-center p-2 hover:bg-sand rounded transition-colors group cursor-pointer">
                <span class="text-2xl group-active:scale-90 transition-transform">💬</span>
                <span class="font-pixel text-xs">CHAT</span>
            </a>
            <a href="/vault"
                class="flex flex-col items-center p-2 hover:bg-sand rounded transition-colors group cursor-pointer">
                <span class="text-2xl group-active:scale-90 transition-transform">🔐</span>
                <span class="font-pixel text-xs">VAULT</span>
            </a>
            <a href="/coach"
                class="flex flex-col items-center p-2 hover:bg-sand rounded transition-colors group cursor-pointer">
                <span class="text-2xl group-active:scale-90 transition-transform">🤖</span>
                <span class="font-pixel text-xs">AI COACH</span>

                <a href="/gifts"
                    class="flex flex-col items-center p-2 hover:bg-sand rounded transition-colors group cursor-pointer">
                    <span class="text-2xl group-active:scale-90 transition-transform">🎁</span>
                    <span class="font-pixel text-xs">GIFTS</span>
                </a>
            </a>
        </div>
    </nav>
</body>

</html>