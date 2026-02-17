<div x-show="showBuildMenu" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="translate-y-full"
     x-transition:enter-end="translate-y-0"
     x-transition:leave="transition ease-in duration-300"
     x-transition:leave-start="translate-y-0"
     x-transition:leave-end="translate-y-full"
     class="fixed bottom-0 left-0 right-0 z-[60] h-[75vh] bg-parchment border-t-8 border-toast flex flex-col shadow-[0_-12px_0_rgba(0,0,0,0.3)]">
    
    <!-- FIXED HEADER: Does not scroll -->
    <div class="p-6 border-b-4 border-sand/50 bg-parchment">
        <div class="flex justify-between items-center mb-6">
            <h3 class="font-pixel text-4xl text-berry">Build Shop</h3>
            <button @click="showBuildMenu = false" class="bg-toast text-white font-pixel px-6 py-2 text-xl border-b-4 border-black/20 active:translate-y-1 active:border-b-0">
                Close
            </button>
        </div>

        <!-- Category Tabs -->
        <div class="flex gap-4">
            <button class="bg-rose text-white border-b-4 border-berry px-6 py-2 font-pixel text-lg uppercase">Decor</button>
            <button class="bg-sand border-b-4 border-toast px-6 py-2 font-pixel text-lg uppercase text-toast">Structures</button>
        </div>
    </div>

    <!-- SCROLLABLE CONTENT AREA -->
    <div class="flex-1 overflow-y-auto p-6 pb-32">
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-6">
            @php
                $items = [
                    ['name' => 'Lantern', 'icon' => '🏮', 'cost' => 5],
                    ['name' => 'Fountain', 'icon' => '⛲', 'cost' => 20],
                    ['name' => 'Bench', 'icon' => '🪑', 'cost' => 10],
                    ['name' => 'Flowers', 'icon' => '🌷', 'cost' => 2],
                    ['name' => 'Gazebo', 'icon' => '🛖', 'cost' => 50],
                    ['name' => 'Stone Path', 'icon' => '🧱', 'cost' => 1],
                    ['name' => 'Statue', 'icon' => '🗿', 'cost' => 100],
                    ['name' => 'Bush', 'icon' => '🌳', 'cost' => 15],
                    ['name' => 'Fence', 'icon' => '🚧', 'cost' => 4],
                    ['name' => 'Bird Bath', 'icon' => '🥣', 'cost' => 12],
                    ['name' => 'Signpost', 'icon' => '🪧', 'cost' => 8],
                    ['name' => 'Picnic Table', 'icon' => '🧺', 'cost' => 25],
                ];
            @endphp

            @foreach($items as $item)
                <div class="bg-white/60 border-4 border-toast p-4 flex flex-col items-center justify-between aspect-square hover:bg-white transition-all cursor-pointer group hover:-translate-y-1 hover:shadow-lg">
                    <span class="text-5xl mb-2 group-hover:scale-110 transition-transform">{{ $item['icon'] }}</span>
                    
                    <div class="text-center">
                        <span class="font-pixel text-sm text-cocoa block leading-none mb-2 uppercase">{{ $item['name'] }}</span>
                        <div class="flex items-center justify-center gap-1 bg-sky/10 px-2 py-1 border border-sky/20">
                            <span class="font-pixel text-sky text-lg leading-none">{{ $item['cost'] }}</span>
                            <span class="text-sm">💧</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Dark Backdrop -->
<div x-show="showBuildMenu" 
     x-transition:enter="transition opacity-0 duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition opacity-100 duration-300"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @click="showBuildMenu = false" 
     class="fixed inset-0 bg-black/60 z-[55] backdrop-blur-sm"></div>