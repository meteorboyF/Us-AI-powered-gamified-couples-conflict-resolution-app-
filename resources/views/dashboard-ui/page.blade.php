<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Us - Dashboard UI</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body x-data="{ showBuildMenu: false }" class="bg-slate-900 h-screen overflow-hidden text-white">
    <x-status-bar :current-couple-id="$currentCoupleId" />

    <main class="h-full w-full overflow-y-auto scroll-smooth pt-20">
        <div class="fixed inset-0 bg-gradient-to-b from-slate-900 to-slate-800 z-0"></div>

        <div class="relative z-10 flex flex-col items-center">
            <div class="h-[35vh]"></div>

            <div class="w-full bg-emerald-800 border-t-4 border-emerald-700 min-h-[55vh] pb-64 shadow-[0_-20px_50px_rgba(0,0,0,0.3)]">
                <div class="grid grid-cols-4 max-w-4xl mx-auto gap-8 px-8 pt-16">
                    <div class="relative h-32 flex flex-col items-center justify-end">
                        <div class="text-7xl drop-shadow-2xl animate-bounce">??</div>
                        <div class="w-16 h-4 bg-black/20 rounded-full blur-sm -mt-2"></div>
                    </div>

                    @for ($i = 0; $i < 3; $i++)
                        <button type="button" @click="showBuildMenu = true"
                            class="w-24 h-24 border-2 border-dashed border-white/25 rounded-xl mt-8 flex items-center justify-center text-white/60 text-4xl hover:bg-white/10 hover:border-white/40 transition-all">
                            +
                        </button>
                    @endfor
                </div>

                <div class="mt-12 text-center">
                    <div class="inline-block bg-amber-100/95 border-2 border-amber-300 px-6 py-3 rounded shadow-lg">
                        <p class="font-semibold text-amber-900">Tap to plant Love Seeds</p>
                    </div>
                </div>

                @if (! $currentCoupleId)
                    <div class="mt-8 max-w-3xl mx-auto px-8">
                        <div class="bg-amber-50 text-amber-900 border border-amber-200 rounded p-4">
                            <p class="font-semibold">No couple selected.</p>
                            <a href="/couple" class="underline">Go to Couple Linking</a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </main>

    <nav class="fixed bottom-6 left-0 right-0 z-50 px-4">
        <div class="max-w-3xl mx-auto bg-amber-100 border-2 border-amber-300 flex justify-around p-2 shadow">
            @php
                $navItems = [
                    ['i' => '???', 'l' => 'Build', 'u' => '#', 'click' => 'showBuildMenu = true'],
                    ['i' => '??', 'l' => 'World', 'u' => '/world-ui'],
                    ['i' => '??', 'l' => 'Missions', 'u' => '/missions-ui'],
                    ['i' => '??', 'l' => 'Chat', 'u' => '/chat'],
                    ['i' => '??', 'l' => 'Vault', 'u' => '/vault-ui'],
                    ['i' => '??', 'l' => 'Coach', 'u' => '/ai-coach'],
                    ['i' => '??', 'l' => 'Gifts', 'u' => '/gifts-ui'],
                ];
            @endphp
            @foreach ($navItems as $item)
                <a
                    @if (isset($item['click']))
                        @click="{{ $item['click'] }}"
                    @else
                        href="{{ $item['u'] }}"
                    @endif
                    class="flex flex-col items-center p-2 hover:bg-amber-200 rounded transition-colors cursor-pointer"
                >
                    <span class="text-xl">{{ $item['i'] }}</span>
                    <span class="text-[11px] uppercase tracking-wide text-amber-900">{{ $item['l'] }}</span>
                </a>
            @endforeach
        </div>
    </nav>

    <x-build-menu />
</body>
</html>
