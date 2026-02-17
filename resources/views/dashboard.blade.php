<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Us — Shared World</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<!-- h-screen overflow-hidden stops the whole browser from scrolling -->
<body x-data="{ showBuildMenu: false }" class="bg-navy h-screen overflow-hidden">

    <x-status-bar />

    <!-- THE SCROLLABLE WINDOW (Middle area only) -->
    <main class="h-full w-full overflow-y-auto scroll-smooth pt-20">
        
        <!-- FIXED SKY: Stays behind everything while you scroll the ground -->
        <div class="fixed inset-0 bg-gradient-to-b from-navy to-[#1a2a44] z-0"></div>

        <!-- THE SCROLLABLE CONTENT -->
        <div class="relative z-10 flex flex-col items-center">
            
            <!-- Spacer to push ground down initially -->
            <div class="h-[40vh]"></div>

            <!-- THE GROUND (The only part that moves when you scroll) -->
            <div class="w-full bg-leaf border-t-8 border-[#3d8c4d] min-h-[50vh] pb-64 shadow-[0_-20px_50px_rgba(0,0,0,0.3)]">
                
                <!-- 4 Slot Grid -->
                <div class="grid grid-cols-4 max-w-4xl mx-auto gap-8 px-12 pt-20">
                    
                    <!-- Slot 1: House -->
                    <div class="relative h-32 flex flex-col items-center justify-end">
                        <div class="text-7xl filter drop-shadow-2xl animate-bounce duration-[3000ms]">🏡</div>
                        <div class="w-16 h-4 bg-black/20 rounded-full blur-sm -mt-2"></div>
                    </div>

                    <!-- Slot 2-4: The Slots -->
                    @for ($i = 0; $i < 3; $i++)
                        <div @click="showBuildMenu = true" 
                             class="w-24 h-24 border-4 border-dashed border-white/20 rounded-xl mt-8 flex items-center justify-center text-white/20 font-pixel text-4xl cursor-pointer hover:bg-white/10 hover:border-white/40 transition-all">
                            +
                        </div>
                    @endfor
                </div>

                <!-- Central Prompt inside the scrollable area -->
                <div class="mt-12 text-center">
                     <div class="inline-block bg-parchment/90 border-4 border-toast px-6 py-3 rounded shadow-lg animate-pulse">
                        <p class="font-pixel text-cocoa text-xl">Tap to plant Love Seeds</p>
                     </div>
                </div>
            </div>
        </div>
    </main>

    <!-- FIXED NAVIGATION: Stays on top, never moves -->
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