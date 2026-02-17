<div x-show="showBuildMenu" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="translate-y-full"
     x-transition:enter-end="translate-y-0"
     x-transition:leave="transition ease-in duration-300"
     x-transition:leave-start="translate-y-0"
     x-transition:leave-end="translate-y-full"
     class="fixed bottom-0 left-0 right-0 z-[60] h-[50vh] bg-parchment border-t-8 border-toast p-6 shadow-[0_-10px_0_rgba(0,0,0,0.2)]">
    
    <!-- Header with Close Button -->
    <div class="flex justify-between items-center mb-6">
        <h3 class="font-pixel text-3xl text-berry">Build Shop</h3>
        <button @click="showBuildMenu = false" class="bg-toast text-white font-pixel px-4 py-1 active:translate-y-1">Close</button>
    </div>

    <!-- Category Tabs -->
    <div class="flex gap-2 mb-6">
        <button class="bg-sand border-b-4 border-toast px-4 py-1 font-pixel text-sm uppercase">Decor</button>
        <button class="bg-sand/50 border-b-4 border-toast/50 px-4 py-1 font-pixel text-sm uppercase opacity-50">Structures</button>
    </div>

    <!-- Items Grid (PDF Page 4: Decor items) -->
    <div class="grid grid-cols-3 gap-4 overflow-y-auto h-full pb-20">
        @php
            $items = [
                ['name' => 'Lantern', 'icon' => '🏮', 'cost' => 5],
                ['name' => 'Fountain', 'icon' => '⛲', 'cost' => 20],
                ['name' => 'Bench', 'icon' => '🪑', 'cost' => 10],
                ['name' => 'Flowers', 'icon' => '🌷', 'cost' => 2],
                ['name' => 'Gazebo', 'icon' => '🛖', 'cost' => 50],
                ['name' => 'Path', 'icon' => '🧱', 'cost' => 1],
            ];
        @endphp

        @foreach($items as $item)
            <div class="bg-white/50 border-4 border-toast p-3 flex flex-col items-center hover:bg-white transition-colors cursor-pointer group">
                <span class="text-4xl mb-2 group-hover:scale-110 transition-transform">{{ $item['icon'] }}</span>
                <span class="font-pixel text-[10px] text-cocoa text-center leading-tight mb-1">{{ $item['name'] }}</span>
                <div class="flex items-center gap-1">
                    <span class="font-pixel text-sky text-xs">{{ $item['cost'] }}</span>
                    <span class="text-[10px]">💧</span>
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Dark Backdrop -->
<div x-show="showBuildMenu" @click="showBuildMenu = false" 
     class="fixed inset-0 bg-black/40 z-[55] transition-opacity"></div>