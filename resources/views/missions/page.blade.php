<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Missions + Daily Check-in
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 space-y-6">
            @if (session('status'))
                <div class="rounded border border-green-300 bg-green-50 px-4 py-3 text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded border border-red-300 bg-red-50 px-4 py-3 text-red-800">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($errorCode)
                <div class="rounded border border-amber-300 bg-amber-50 px-4 py-4 text-amber-900">
                    <p class="font-semibold">{{ $errorMessage }}</p>
                    <a href="{{ url('/couple') }}" class="mt-2 inline-block underline">Go to Couple Linking</a>
                </div>
            @else
                <section class="rounded border bg-white p-4 space-y-4">
                    <h3 class="text-lg font-semibold">Today Check-in</h3>

                    <form method="POST" action="{{ route('checkins.store') }}" class="space-y-3">
                        @csrf
                        <div>
                            <label for="mood" class="block text-sm font-medium text-gray-700">Mood</label>
                            <select id="mood" name="mood" class="mt-1 block w-full rounded border-gray-300">
                                @php($moods = ['great', 'good', 'okay', 'low', 'bad'])
                                @foreach ($moods as $mood)
                                    <option value="{{ $mood }}" @selected(old('mood', $ownCheckin?->mood) === $mood)>
                                        {{ ucfirst($mood) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="note" class="block text-sm font-medium text-gray-700">Note</label>
                            <textarea id="note" name="note" rows="3" class="mt-1 block w-full rounded border-gray-300">{{ old('note', $ownCheckin?->note) }}</textarea>
                        </div>

                        <button type="submit" class="rounded bg-indigo-600 px-4 py-2 text-white">Save Check-in</button>
                    </form>

                    <div class="pt-2 border-t">
                        <p class="font-medium">Partner Check-in</p>
                        @if ($partnerCheckin)
                            <p class="text-sm text-gray-700">
                                {{ $partnerCheckin->user?->name ?? 'Partner' }}: {{ ucfirst($partnerCheckin->mood) }}
                                @if ($partnerCheckin->note)
                                    - {{ $partnerCheckin->note }}
                                @endif
                            </p>
                        @else
                            <p class="text-sm text-gray-500">No partner check-in yet today.</p>
                        @endif
                    </div>
                </section>

                <section class="rounded border bg-white p-4 space-y-3">
                    <h3 class="text-lg font-semibold">Assigned Missions</h3>

                    @forelse ($missions as $mission)
                        <div class="rounded border px-3 py-3 flex items-center justify-between gap-4">
                            <div>
                                <p class="font-medium">{{ $mission['title'] }}</p>
                                <p class="text-xs text-gray-500">{{ $mission['key'] }} · {{ $mission['cadence'] }}</p>
                                <p class="text-sm {{ $mission['today_completed'] ? 'text-green-700' : 'text-amber-700' }}">
                                    {{ $mission['today_completed'] ? 'Completed today' : 'Not completed today' }}
                                </p>
                            </div>

                            <form method="POST" action="{{ route('missions.complete') }}" class="space-y-2">
                                @csrf
                                <input type="hidden" name="couple_mission_id" value="{{ $mission['id'] }}">
                                <input type="text" name="notes" value="{{ old('notes') }}" placeholder="Optional note"
                                       class="block rounded border-gray-300 text-sm">
                                <button type="submit" class="rounded bg-slate-800 px-3 py-2 text-white text-sm">
                                    Complete Today
                                </button>
                            </form>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No assigned missions yet.</p>
                    @endforelse
                </section>
            @endif
        </div>
    </div>
</x-app-layout>
