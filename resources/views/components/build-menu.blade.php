<div
    x-show="showBuildMenu"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="translate-y-full"
    x-transition:enter-end="translate-y-0"
    x-transition:leave="transition ease-in duration-300"
    x-transition:leave-start="translate-y-0"
    x-transition:leave-end="translate-y-full"
    class="fixed bottom-0 left-0 right-0 z-[60] h-[75vh] bg-white border-t-4 border-slate-300 flex flex-col shadow-[0_-12px_30px_rgba(0,0,0,0.25)]"
>
    <div class="p-6 border-b border-slate-200 bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-2xl font-bold text-slate-900">Build Shop</h3>
            <button type="button" @click="showBuildMenu = false" class="px-4 py-2 bg-slate-700 text-white rounded">Close</button>
        </div>
    </div>

    <div class="flex-1 overflow-y-auto p-6 pb-32">
        @php
            $items = [
                ['name' => 'Lantern', 'icon' => '??', 'cost' => 5],
                ['name' => 'Fountain', 'icon' => '?', 'cost' => 20],
                ['name' => 'Bench', 'icon' => '??', 'cost' => 10],
                ['name' => 'Flowers', 'icon' => '??', 'cost' => 2],
                ['name' => 'Gazebo', 'icon' => '??', 'cost' => 50],
                ['name' => 'Stone Path', 'icon' => '??', 'cost' => 1],
            ];
        @endphp

        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            @foreach ($items as $item)
                <div class="border border-slate-200 rounded p-4 bg-slate-50 flex flex-col items-center gap-2">
                    <span class="text-4xl">{{ $item['icon'] }}</span>
                    <span class="text-sm font-semibold text-slate-900">{{ $item['name'] }}</span>
                    <span class="text-xs text-slate-600">{{ $item['cost'] }} seeds</span>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div
    x-show="showBuildMenu"
    x-transition:enter="transition opacity-0 duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition opacity-100 duration-300"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    @click="showBuildMenu = false"
    class="fixed inset-0 bg-black/50 z-[55]"
></div>
