<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Gift Suggestions
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
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <section class="lg:col-span-1 bg-white shadow-sm sm:rounded-lg p-4">
                        <h3 class="font-semibold text-gray-900 mb-3">Create request</h3>
                        <p class="text-sm text-gray-500">Request form will appear here.</p>

                        <h3 class="font-semibold text-gray-900 mt-6 mb-3">Recent requests</h3>
                        <div class="space-y-2">
                            @forelse ($requests as $giftRequest)
                                <a href="{{ route('gifts.ui', ['request_id' => $giftRequest->id]) }}"
                                    class="block border rounded px-3 py-2 text-sm border-gray-200 hover:bg-gray-50">
                                    <div class="font-medium">{{ $giftRequest->occasion }}</div>
                                    <div class="text-gray-500">#{{ $giftRequest->id }}</div>
                                </a>
                            @empty
                                <p class="text-sm text-gray-500">No requests yet.</p>
                            @endforelse
                        </div>
                    </section>

                    <section class="lg:col-span-2 bg-white shadow-sm sm:rounded-lg p-4">
                        @if (! $selectedRequest)
                            <p class="text-sm text-gray-500">Select a request to see details and suggestions.</p>
                        @else
                            <h3 class="font-semibold text-gray-900">Request #{{ $selectedRequest->id }}</h3>
                            <p class="text-sm text-gray-600">Occasion: {{ $selectedRequest->occasion }}</p>
                            <div class="mt-4">
                                <p class="text-sm text-gray-500">Generate and favorites controls will appear here.</p>
                            </div>
                        @endif
                    </section>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
