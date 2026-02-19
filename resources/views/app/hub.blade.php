<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">App Hub</h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl px-4 space-y-4">
            @if ($currentCoupleId)
                <div class="rounded border border-emerald-300 bg-emerald-50 px-4 py-3 text-emerald-900">
                    Current Couple ID: {{ $currentCoupleId }}
                </div>
            @else
                <div class="rounded border border-amber-300 bg-amber-50 px-4 py-3 text-amber-900">
                    <p class="font-semibold">No couple selected</p>
                    <a href="{{ url('/couple') }}" class="mt-2 inline-block underline">Go to Couple Linking</a>
                </div>
            @endif

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                <a href="{{ url('/couple') }}" class="rounded border px-4 py-3 hover:bg-gray-50">Couple</a>
                <a href="{{ url('/world-ui') }}" class="rounded border px-4 py-3 hover:bg-gray-50">World</a>
                <a href="{{ url('/missions-ui') }}" class="rounded border px-4 py-3 hover:bg-gray-50">Missions</a>
                <a href="{{ url('/chat') }}" class="rounded border px-4 py-3 hover:bg-gray-50">Chat</a>
                <a href="{{ url('/vault-ui') }}" class="rounded border px-4 py-3 hover:bg-gray-50">Vault</a>
                <a href="{{ url('/ai-coach') }}" class="rounded border px-4 py-3 hover:bg-gray-50">AI Coach</a>
                <a href="{{ url('/gifts-ui') }}" class="rounded border px-4 py-3 hover:bg-gray-50">Gifts</a>
            </div>
        </div>
    </div>
</x-app-layout>
