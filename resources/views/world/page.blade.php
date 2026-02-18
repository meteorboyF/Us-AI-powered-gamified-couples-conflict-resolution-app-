<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            World V1
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if ($statusCode === 409)
                <div class="bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded">
                    <p class="font-semibold">No couple selected.</p>
                    <p class="text-sm mt-1">Select or create a couple to access your world.</p>
                    <a href="/couple" class="underline mt-2 inline-block">Go to Couple Linking</a>
                </div>
            @elseif ($statusCode === 403)
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                    <p class="font-semibold">Not authorized for current couple.</p>
                    <p class="text-sm mt-1">{{ $statusMessage ?: 'Please select an accessible couple.' }}</p>
                    <a href="/couple" class="underline mt-2 inline-block">Go to Couple Linking</a>
                </div>
            @elseif ($world)
                <div class="bg-white shadow-sm rounded-lg border p-6">
                    <div class="flex flex-wrap gap-4 items-end justify-between">
                        <div class="space-y-1">
                            <h3 class="text-lg font-semibold text-gray-900">World State</h3>
                            <p class="text-sm text-gray-600">Vibe: <span id="world-vibe">{{ $world['vibe'] }}</span></p>
                            <p class="text-sm text-gray-600">Level: {{ $world['level'] }} | XP: {{ $world['xp'] }}</p>
                        </div>
                        <form id="vibe-form" class="flex gap-2 items-center">
                            @csrf
                            <label for="vibe" class="text-sm text-gray-700">Change vibe</label>
                            <select id="vibe" name="vibe" class="rounded-md border-gray-300">
                                @foreach (['neutral', 'warm', 'playful', 'tense', 'repair'] as $vibe)
                                    <option value="{{ $vibe }}" @selected($world['vibe'] === $vibe)>{{ ucfirst($vibe) }}</option>
                                @endforeach
                            </select>
                            <x-pixel-button type="submit">Update</x-pixel-button>
                        </form>
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded-lg border p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">World Items</h3>
                    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach ($world['items'] as $item)
                            <div class="border rounded-lg p-4 bg-gray-50">
                                <p class="font-semibold text-gray-900">{{ $item['title'] }}</p>
                                <p class="text-xs text-gray-500 mb-3">{{ $item['key'] }}</p>
                                <p class="text-sm text-gray-700">{{ $item['description'] ?: 'No description yet.' }}</p>
                                <div class="mt-4">
                                    @if ($item['unlocked'])
                                        <span class="inline-flex px-2 py-1 rounded bg-green-100 text-green-700 text-xs font-medium">Unlocked</span>
                                    @else
                                        <form class="unlock-form" data-key="{{ $item['key'] }}">
                                            @csrf
                                            <x-pixel-button type="submit" variant="secondary">Unlock</x-pixel-button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if ($world)
        <script>
            const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const vibeForm = document.getElementById('vibe-form');

            vibeForm?.addEventListener('submit', async (event) => {
                event.preventDefault();
                const vibe = document.getElementById('vibe').value;

                const response = await fetch('/world/vibe', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                    body: JSON.stringify({ vibe }),
                });

                if (response.ok) {
                    window.location.reload();
                }
            });

            document.querySelectorAll('.unlock-form').forEach((form) => {
                form.addEventListener('submit', async (event) => {
                    event.preventDefault();
                    const key = form.dataset.key;

                    const response = await fetch('/world/unlock', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                        },
                        body: JSON.stringify({ key }),
                    });

                    if (response.ok) {
                        window.location.reload();
                    }
                });
            });
        </script>
    @endif
</x-app-layout>
