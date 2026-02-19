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

        @if ($statusCode === 409 || ! $world)
            <div class="relative z-10 pt-24 px-8 max-w-4xl mx-auto">
                <div class="bg-amber-50 text-amber-900 border border-amber-200 rounded p-4">
                    <p class="font-semibold">No couple selected.</p>
                    <p class="text-sm mt-1">{{ $statusMessage ?: 'Select or create a couple to view your world scene.' }}</p>
                    <a href="/couple" class="underline">Go to Couple Linking</a>
                </div>
            </div>
        @elseif ($statusCode === 403)
            <div class="relative z-10 pt-24 px-8 max-w-4xl mx-auto">
                <div class="bg-red-50 text-red-700 border border-red-200 rounded p-4">
                    <p class="font-semibold">Not authorized for current couple.</p>
                    <p class="text-sm mt-1">{{ $statusMessage ?: 'Please switch to an accessible couple.' }}</p>
                    <a href="/couple" class="underline">Go to Couple Linking</a>
                </div>
            </div>
        @else
            @include('world.scene', ['world' => $world])
        @endif
    </main>

    <nav class="fixed bottom-6 left-0 right-0 z-50 px-4">
        <div class="max-w-3xl mx-auto bg-amber-100 border-2 border-amber-300 flex justify-around p-2 shadow">
            @php
                $navItems = [
                    ['l' => 'Build', 'u' => '#', 'click' => 'showBuildMenu = true'],
                    ['l' => 'World', 'u' => '/world-ui'],
                    ['l' => 'Missions', 'u' => '/missions-ui'],
                    ['l' => 'Chat', 'u' => '/chat'],
                    ['l' => 'Vault', 'u' => '/vault-ui'],
                    ['l' => 'Coach', 'u' => '/ai-coach'],
                    ['l' => 'Gifts', 'u' => '/gifts-ui'],
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
                    <span class="text-[11px] uppercase tracking-wide text-amber-900">{{ $item['l'] }}</span>
                </a>
            @endforeach
        </div>
    </nav>

    <x-build-menu />

    @if ($world)
        <script>
            const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const vibeForm = document.getElementById('dashboard-vibe-form');
            const vibeLabel = document.getElementById('dashboard-vibe');
            const unlockButtons = document.querySelectorAll('.dashboard-unlock-btn');

            vibeForm?.addEventListener('submit', async (event) => {
                event.preventDefault();
                const vibe = document.getElementById('dashboard-vibe-input').value;

                const response = await fetch('/world/vibe', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                    body: JSON.stringify({ vibe }),
                });

                if (response.ok) {
                    vibeLabel.textContent = vibe;
                }
            });

            unlockButtons.forEach((button) => {
                button.addEventListener('click', async () => {
                    const key = button.dataset.key;

                    const response = await fetch('/world/unlock', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            Accept: 'application/json',
                            'X-CSRF-TOKEN': csrf,
                        },
                        body: JSON.stringify({ key }),
                    });

                    if (response.ok) {
                        button.textContent = 'Unlocked';
                        button.disabled = true;
                        button.classList.add('opacity-70');
                    }
                });
            });
        </script>
    @endif
</body>
</html>
