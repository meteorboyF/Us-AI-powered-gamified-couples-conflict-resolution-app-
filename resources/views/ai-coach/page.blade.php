<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            AI Coach
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (! $coupleId)
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <p class="text-gray-900 font-semibold">No couple selected</p>
                    <a href="/couple" class="text-indigo-600 hover:underline mt-2 inline-block">Go to couple setup</a>
                </div>
            @else
                <div id="ai-coach-root"
                    data-couple-id="{{ $coupleId }}"
                    data-session-id="{{ $currentSession?->id }}"
                    class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <section class="lg:col-span-1 bg-white shadow-sm sm:rounded-lg p-4">
                        <h3 class="font-semibold text-gray-900 mb-3">Sessions</h3>
                        <div class="space-y-2">
                            @forelse ($sessions as $session)
                                <a href="{{ route('ai.coach.page', ['session' => $session->id]) }}"
                                    class="block border rounded px-3 py-2 text-sm {{ $currentSession?->id === $session->id ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200' }}">
                                    <div class="font-medium">{{ strtoupper($session->mode) }}</div>
                                    <div class="text-gray-500">{{ $session->status }}</div>
                                </a>
                            @empty
                                <p class="text-sm text-gray-500">No sessions yet.</p>
                            @endforelse
                        </div>
                    </section>

                    <section class="lg:col-span-2 bg-white shadow-sm sm:rounded-lg p-4">
                        <p class="text-sm text-gray-500">Session content will appear here.</p>
                    </section>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
