<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Couple</h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl px-4">
            @if ($currentCoupleId)
                <div class="rounded border border-emerald-300 bg-emerald-50 px-4 py-3 text-emerald-900">
                    Current Couple ID: {{ $currentCoupleId }}
                </div>
            @else
                <div class="rounded border border-amber-300 bg-amber-50 px-4 py-3 text-amber-900">
                    <p class="font-semibold">No couple selected</p>
                    <p class="text-sm mt-1">Create or join a couple to continue.</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
