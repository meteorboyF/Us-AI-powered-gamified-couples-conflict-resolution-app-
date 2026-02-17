<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Couple Linking
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-3">
                    <h3 class="font-semibold text-lg">Create Couple</h3>
                    <form method="POST" action="{{ route('couples.store') }}" class="space-y-3">
                        @csrf
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Couple Name (Optional)</label>
                            <input id="name" name="name" type="text" maxlength="255" class="mt-1 w-full rounded-md border-gray-300" value="{{ old('name') }}">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <x-pixel-button type="submit">Create</x-pixel-button>
                    </form>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-3">
                    <h3 class="font-semibold text-lg">Join By Invite Code</h3>
                    <form method="POST" action="{{ route('couples.join') }}" class="space-y-3">
                        @csrf
                        <div>
                            <label for="invite_code" class="block text-sm font-medium text-gray-700">Invite Code</label>
                            <input id="invite_code" name="invite_code" type="text" required maxlength="32" class="mt-1 w-full rounded-md border-gray-300" value="{{ old('invite_code') }}">
                            @error('invite_code')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <x-pixel-button type="submit" variant="secondary">Join</x-pixel-button>
                    </form>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-3">
                    <h3 class="font-semibold text-lg">Switch Current Couple</h3>
                    <form method="POST" action="{{ route('couples.switch') }}" class="space-y-3">
                        @csrf
                        <div>
                            <label for="couple_id" class="block text-sm font-medium text-gray-700">Select Couple</label>
                            <select id="couple_id" name="couple_id" class="mt-1 w-full rounded-md border-gray-300">
                                @foreach ($couples as $couple)
                                    <option value="{{ $couple->id }}" @selected((int) $currentCoupleId === (int) $couple->id)>
                                        {{ $couple->name ?: 'Couple #'.$couple->id }}
                                    </option>
                                @endforeach
                            </select>
                            @error('couple_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <x-pixel-button type="submit" variant="ghost">Switch</x-pixel-button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
