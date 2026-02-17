<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Us — Shared World</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body x-data="{ showBuildMenu: false }" class="bg-navy h-screen overflow-hidden">

    <x-status-bar />

    <!-- GAME WORLD (Fixed Screen) -->
    <main class="relative h-full w-full flex items-center justify-center">
        
        <!-- Sky Layer -->
        <div class="absolute inset-0 bg-gradient-to-b from-navy to-[#1a2a44]"></div>

        <!-- Ground Layer (Fixed at bottom) -->
        <div class="absolute bottom-0 w-full h-48 bg-leaf border-t-8 border-[#3d8c4d]">
            <!-- 4 Slot Grid -->
            <div class="grid grid-cols-4 h-full max-w-4xl mx-auto gap-4 p-8">
                
                <!-- Slot 1: House -->
                <div class="flex flex-col items-center justify-end h-24">
                    <span class="text-6xl animate-bounce duration-[3000ms]">🏡</span>
                    <div class="w-12 h-3 bg-black/20 rounded-full blur-sm"></div>
                </div>

                <!-- Slots 2-4: Placeholders -->
                <div @click="showBuildMenu = true" class="border-2 border-dashed border-white/20 rounded-lg hover:bg-white/10 transition-colors cursor-pointer flex items-center justify-center text-white/20 font-pixel text-4xl h-24 mt-auto">+</div>
                <div @click="showBuildMenu = true" class="border-2 border-dashed border-white/20 rounded-lg hover:bg-white/10 transition-colors cursor-pointer flex items-center justify-center text-white/20 font-pixel text-4xl h-24 mt-auto">+</div>
                <div @click="showBuildMenu = true" class="border-2 border-dashed border-white/20 rounded-lg hover:bg-white/10 transition-colors cursor-pointer flex items-center justify-center text-white/20 font-pixel text-4xl h-24 mt-auto">+</div>
            </div>
        </div>

        <!-- Central Prompt -->
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2">
             <div class="bg-parchment/90 border-4 border-toast px-6 py-3 rounded shadow-lg animate-pulse">
                <p class="font-pixel text-cocoa text-xl">Tap to plant Love Seeds</p>
             </div>
        </div>
    </main>

    <!-- Navigation -->
    <nav class="fixed bottom-6 left-0 right-0 z-50 px-4">
        <div class="max-w-md mx-auto bg-parchment border-4 border-toast flex justify-around p-2 shadow-[0_6px_0_rgba(0,0,0,0.2)]">
            @php
                $navItems = [
                    ['i' => '🛠️', 'l' => 'BUILD', 'u' => '#', 'click' => 'showBuildMenu = true'],
                    ['i' => '📜', 'l' => 'MISSIONS', 'u' => '/missions'],
                    ['i' => '💬', 'l' => 'CHAT', 'u' => '/chat'],
                    ['i' => '🔐', 'l' => 'VAULT', 'u' => '/vault'],
                    ['i' => '🤖', 'l' => 'COACH', 'u' => '/coach'],
                    ['i' => '🎁', 'l' => 'GIFTS', 'u' => '/gifts'],
                ];
            @endphp
            @foreach($navItems as $item)
                <a @if(isset($item['click'])) @click="{{ $item['click'] }}" @else href="{{ $item['u'] }}" @endif 
                   class="flex flex-col items-center p-2 hover:bg-sand rounded transition-colors group cursor-pointer">
                    <span class="text-2xl group-active:scale-90 transition-transform">{{ $item['i'] }}</span>
                    <span class="font-pixel text-[10px] text-cocoa uppercase">{{ $item['l'] }}</span>
                </a>
            @endforeach
        </div>
    </nav>

    <x-build-menu />

</body>
</html>