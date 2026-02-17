<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Us — Shared World</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<!-- Alpine.js State: seeds starts at 42, isPlanted starts as false -->
<body x-data="{ seeds: 42, isPlanted: false }" class="bg-navy h-screen overflow-hidden">

    <x-status-bar />

    <!-- GAME WORLD -->
    <main class="relative h-full w-full flex items-center justify-center">
        
        <!-- Sky Layer -->
        <div class="absolute inset-0 bg-gradient-to-b from-navy to-[#1a2a44]"></div>

        <!-- Ground Layer (Increased height to prevent nav overlap) -->
        <div class="absolute bottom-0 w-full h-[40vh] bg-leaf border-t-8 border-[#3d8c4d]">
            
            <!-- Items Grid -->
            <div class="grid grid-cols-4 h-full max-w-5xl mx-auto gap-8 px-12 pt-16">
                
                <!-- Slot 1: The House -->
                <div class="relative h-32 flex flex-col items-center justify-end">
                    <div class="text-6xl filter drop-shadow-xl animate-bounce duration-[3000ms]">🏡</div>
                    <div class="w-16 h-4 bg-black/20 rounded-full blur-sm -mt-2"></div>
                </div>

                <!-- Slot 2: The Interactive Planting Slot -->
                <div class="relative h-32 flex flex-col items-center justify-end">
                    <!-- If NOT planted, show the ghost placeholder -->
                    <template x-if="!isPlanted">
                        <div class="w-24 h-24 border-4 border-dashed border-white/20 rounded-xl flex items-center justify-center text-white/20 font-pixel text-4xl cursor-pointer hover:bg-white/10 transition-all"
                             @click="isPlanted = true; seeds--">
                            +
                        </div>
                    </template>

                    <!-- If PLANTED, show the flower -->
                    <template x-if="isPlanted">
                        <div class="flex flex-col items-center animate-bounce">
                            <div class="text-5xl">🌻</div>
                            <div class="w-10 h-3 bg-black/20 rounded-full blur-sm"></div>
                        </div>
                    </template>
                </div>

                <!-- Slot 3: Empty -->
                <div class="w-24 h-24 border-4 border-dashed border-white/10 rounded-xl mt-8"></div>
                
                <!-- Slot 4: Empty -->
                <div class="w-24 h-24 border-4 border-dashed border-white/10 rounded-xl mt-8"></div>
            </div>
        </div>

        <!-- Floating Prompt (Disappears when planted) -->
        <template x-if="!isPlanted">
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 z-20">
                <button @click="isPlanted = true; seeds--" 
                        class="bg-parchment/90 border-4 border-toast px-6 py-3 rounded shadow-xl animate-pulse cursor-pointer hover:scale-110 transition-transform">
                    <p class="font-pixel text-cocoa text-xl">Tap to plant Love Seeds</p>
                </button>
            </div>
        </template>

    </main>

    <!-- THE OLD BOTTOM MENU (REINSTATED) -->
    <nav class="fixed bottom-6 left-0 right-0 z-50 px-4">
        <div class="max-w-md mx-auto bg-parchment border-4 border-toast flex justify-around p-2 shadow-[0_6px_0_rgba(0,0,0,0.2)]">
            <a href="#" class="flex flex-col items-center p-2 hover:bg-sand rounded transition-colors group">
                <span class="text-2xl group-active:scale-90 transition-transform">🛠️</span>
                <span class="font-pixel text-[10px] text-cocoa">BUILD</span>
            </a>
            <a href="/missions" class="flex flex-col items-center p-2 hover:bg-sand rounded transition-colors group">
                <span class="text-2xl group-active:scale-90 transition-transform">📜</span>
                <span class="font-pixel text-[10px] text-cocoa">MISSIONS</span>
            </a>
            <a href="/chat" class="flex flex-col items-center p-2 hover:bg-sand rounded transition-colors group">
                <span class="text-2xl group-active:scale-90 transition-transform">💬</span>
                <span class="font-pixel text-[10px] text-cocoa">CHAT</span>
            </a>
            <a href="/vault" class="flex flex-col items-center p-2 hover:bg-sand rounded transition-colors group">
                <span class="text-2xl group-active:scale-90 transition-transform">🔐</span>
                <span class="font-pixel text-[10px] text-cocoa">VAULT</span>
            </a>
            <a href="/coach" class="flex flex-col items-center p-2 hover:bg-sand rounded transition-colors group">
                <span class="text-2xl group-active:scale-90 transition-transform">🤖</span>
                <span class="font-pixel text-[10px] text-cocoa">COACH</span>
            </a>
            <a href="/gifts" class="flex flex-col items-center p-2 hover:bg-sand rounded transition-colors group">
                <span class="text-2xl group-active:scale-90 transition-transform">🎁</span>
                <span class="font-pixel text-[10px] text-cocoa">GIFTS</span>
            </a>
        </div>
    </nav>

</body>
</html>