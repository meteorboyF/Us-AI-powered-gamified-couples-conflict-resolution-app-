<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Gift Suggestions
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-2 rounded">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-2 rounded">
                    {{ $errors->first() }}
                </div>
            @endif

            @if (! $coupleId)
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <p class="text-gray-900 font-semibold">No couple selected</p>
                    <a href="/couple" class="text-indigo-600 hover:underline mt-2 inline-block">Go to couple setup</a>
                </div>
            @else
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <section class="lg:col-span-1 bg-white shadow-sm sm:rounded-lg p-4">
                        <h3 class="font-semibold text-gray-900 mb-3">Create request</h3>
                        <form method="POST" action="{{ route('gifts.ui.request') }}" class="space-y-3">
                            @csrf
                            <div>
                                <label for="occasion" class="block text-sm font-medium text-gray-700">Occasion</label>
                                <select id="occasion" name="occasion" class="w-full border-gray-300 rounded-md shadow-sm mt-1" required>
                                    @foreach (['anniversary', 'sorry', 'comfort', 'surprise', 'birthday', 'date_night'] as $occasion)
                                        <option value="{{ $occasion }}">{{ ucfirst(str_replace('_', ' ', $occasion)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label for="budget_min" class="block text-sm font-medium text-gray-700">Budget min</label>
                                    <input id="budget_min" type="number" name="budget_min" min="0" class="w-full border-gray-300 rounded-md shadow-sm mt-1">
                                </div>
                                <div>
                                    <label for="budget_max" class="block text-sm font-medium text-gray-700">Budget max</label>
                                    <input id="budget_max" type="number" name="budget_max" min="0" class="w-full border-gray-300 rounded-md shadow-sm mt-1">
                                </div>
                            </div>
                            <div>
                                <label for="time_constraint" class="block text-sm font-medium text-gray-700">Time constraint</label>
                                <input id="time_constraint" type="text" name="time_constraint" class="w-full border-gray-300 rounded-md shadow-sm mt-1" placeholder="today / this week">
                            </div>
                            <div>
                                <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                                <textarea id="notes" name="notes" rows="3" class="w-full border-gray-300 rounded-md shadow-sm mt-1"></textarea>
                            </div>
                            <button type="submit" class="w-full px-3 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">
                                Create request
                            </button>
                        </form>

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
                            <p class="text-sm text-gray-600">Budget: {{ $selectedRequest->budget_min ?? 0 }} - {{ $selectedRequest->budget_max ?? 0 }}</p>
                            <p class="text-sm text-gray-600">Time: {{ $selectedRequest->time_constraint ?: 'n/a' }}</p>
                            <p class="text-sm text-gray-600">Notes: {{ $selectedRequest->notes ?: 'n/a' }}</p>

                            <div class="mt-4 mb-4">
                                <form method="POST" action="{{ route('gifts.ui.generate', ['giftRequest' => $selectedRequest->id]) }}">
                                    @csrf
                                    <button type="submit" class="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">
                                        Generate Suggestions
                                    </button>
                                </form>
                            </div>

                            <div class="space-y-3">
                                @forelse ($suggestions as $suggestion)
                                    <div class="border rounded p-3 bg-gray-50">
                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <p class="font-semibold text-gray-900">{{ $suggestion->title }}</p>
                                                <p class="text-sm text-gray-600">{{ $suggestion->category }} | {{ $suggestion->price_band }}</p>
                                            </div>
                                            <form method="POST" action="{{ route('gifts.ui.favorite', ['suggestion' => $suggestion->id]) }}">
                                                @csrf
                                                <button type="submit" class="px-3 py-1 rounded text-sm {{ $suggestion->is_favorite ? 'bg-yellow-500 text-white' : 'bg-gray-200 text-gray-800' }}">
                                                    {{ $suggestion->is_favorite ? 'Unfavorite' : 'Favorite' }}
                                                </button>
                                            </form>
                                        </div>
                                        <p class="mt-2 text-sm text-gray-800">{{ $suggestion->rationale }}</p>
                                        @if ($suggestion->personalization_tip)
                                            <p class="mt-1 text-sm text-gray-600">Tip: {{ $suggestion->personalization_tip }}</p>
                                        @endif
                                    </div>
                                @empty
                                    <p class="text-sm text-gray-500">No suggestions yet. Generate to begin.</p>
                                @endforelse
                            </div>
                        @endif
                    </section>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
