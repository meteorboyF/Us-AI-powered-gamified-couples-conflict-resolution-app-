<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Us — Shared World</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<!-- Alpine.js State: seeds starts at 42, isPlanted starts as false -->

<body x-data="{ seeds: 42, isPlanted: false, showBuildMenu: false }" class="bg-navy min-h-screen overflow-y-auto">    
<x-status-bar />

<!-- GAME WORLD -->
    <!-- Removed h-full to let it expand naturally -->
    <main class="relative w-full flex flex-col items-center justify-start pt-32 pb-48">
        
        <!-- Sky Layer (Fixed in background) -->
        <div class="fixed inset-0 bg-gradient-to-b from-navy to-[#1a2a44] z-0"></div>

        <!-- Ground Layer -->
        <!-- Changed from absolute to relative and increased height -->
        <div class="relative z-10 w-full h-[60vh] bg-leaf border-t-8 border-[#3d8c4d] mt-[40vh] shadow-[0_-20px_50px_rgba(0,0,0,0.3)]">
            
            <!-- Items Grid -->
            <div class="grid grid-cols-4 max-w-5xl mx-auto gap-8 px-12 pt-20">
                
                <!-- Slot 1: The House -->
                <div class="relative h-32 flex flex-col items-center justify-end group">
                    <div class="text-7xl filter drop-shadow-2xl animate-bounce duration-[3000ms]">🏡</div>
                    <div class="w-16 h-4 bg-black/20 rounded-full blur-sm -mt-2"></div>
                </div>

                <!-- Slot 2: Interactive Slot -->
                <div class="relative h-32 flex flex-col items-center justify-end">
                    <template x-if="!isPlanted">
                        <div class="w-24 h-24 border-4 border-dashed border-white/20 rounded-xl flex items-center justify-center text-white/20 font-pixel text-4xl cursor-pointer hover:bg-white/10 transition-all"
                             @click="isPlanted = true; seeds--">
                            +
                        </div>
                    </template>
                    <template x-if="isPlanted">
                        <div class="flex flex-col items-center animate-bounce">
                            <div class="text-6xl">🌻</div>
                            <div class="w-10 h-3 bg-black/20 rounded-full blur-sm"></div>
                        </div>
                    </template>
                </div>

                <!-- Slot 3 & 4: Empty -->
                <div @click="showBuildMenu = true" class="w-24 h-24 border-4 border-dashed border-white/10 rounded-xl mt-8 cursor-pointer flex items-center justify-center text-white/10 font-pixel text-4xl hover:border-white/30">+</div>
                <div @click="showBuildMenu = true" class="w-24 h-24 border-4 border-dashed border-white/10 rounded-xl mt-8 cursor-pointer flex items-center justify-center text-white/10 font-pixel text-4xl hover:border-white/30">+</div>
            </div>

            <!-- Extra room at the very bottom so we can scroll PAST the nav bar -->
            <div class="h-64"></div>
        </div>

        <!-- Floating Prompt (Moved to be relative to the scroll) -->
        <template x-if="!isPlanted">
            <div class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 z-20">
                <button @click="isPlanted = true; seeds--" 
                        class="bg-parchment/90 border-4 border-toast px-8 py-4 rounded shadow-[8px_8px_0_rgba(0,0,0,0.3)] hover:scale-105 active:scale-95 transition-transform cursor-pointer">
                    <p class="font-pixel text-cocoa text-2xl">Tap to plant Love Seeds</p>
                </button>
            </div>
        </template>

    </main>

    <!-- THE OLD BOTTOM MENU (REINSTATED) -->
    <nav class="fixed bottom-6 left-0 right-0 z-50 px-4">
        <div
            class="max-w-md mx-auto bg-parchment border-4 border-toast flex justify-around p-2 shadow-[0_6px_0_rgba(0,0,0,0.2)]">
            <button @click="showBuildMenu = true"
                class="flex flex-col items-center p-2 hover:bg-sand rounded transition-colors group">
                <span class="text-2xl group-active:scale-90 transition-transform">🛠️</span>
                <span class="font-pixel text-[10px] text-cocoa uppercase">Build</span>
            </button>
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
    <x-build-menu />
</body>

</html>