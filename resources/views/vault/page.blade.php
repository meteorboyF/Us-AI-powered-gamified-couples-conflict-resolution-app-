<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Vault</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="rounded-md bg-green-100 text-green-800 px-4 py-3 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="rounded-md bg-red-100 text-red-800 px-4 py-3 text-sm">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-md bg-red-100 text-red-800 px-4 py-3 text-sm">
                    <ul class="list-disc ml-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($errorState === 'no_couple')
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <p class="text-gray-800">No couple selected.</p>
                    <a href="/couple" class="text-indigo-600 underline">Go to couple setup</a>
                </div>
            @elseif ($errorState === 'not_authorized')
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <p class="text-gray-800">Not authorized.</p>
                    <a href="/couple" class="text-indigo-600 underline">Go to couple setup</a>
                </div>
            @else
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <h3 class="font-semibold text-gray-900 mb-4">Create Vault Item</h3>
                    <form method="POST" action="{{ route('vault.ui.create') }}" class="space-y-3">
                        @csrf
                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700">Type</label>
                            <select id="type" name="type" class="mt-1 block w-full border-gray-300 rounded-md">
                                <option value="note">note</option>
                                <option value="reason">reason</option>
                            </select>
                        </div>
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                            <input id="title" name="title" type="text" class="mt-1 block w-full border-gray-300 rounded-md" />
                        </div>
                        <div>
                            <label for="body" class="block text-sm font-medium text-gray-700">Body</label>
                            <textarea id="body" name="body" rows="3" class="mt-1 block w-full border-gray-300 rounded-md" required></textarea>
                        </div>
                        <div class="flex items-center gap-2">
                            <input id="is_sensitive" name="is_sensitive" type="checkbox" value="1" class="rounded border-gray-300 text-indigo-600">
                            <label for="is_sensitive" class="text-sm text-gray-700">Mark as sensitive</label>
                        </div>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md">Create</button>
                    </form>
                </div>

                <div class="space-y-4">
                    @forelse ($items as $item)
                        <div class="bg-white shadow-sm rounded-lg p-6">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="font-semibold text-gray-900">{{ $item['title'] ?: 'Untitled item' }}</h4>
                                    <p class="text-sm text-gray-600 mt-1">
                                        {{ strtoupper($item['type']) }} • by {{ $item['creator_name'] }} • {{ $item['created_at']?->format('Y-m-d H:i') }}
                                    </p>
                                </div>
                                @if ($item['redacted'])
                                    <span class="text-xs font-medium px-2 py-1 bg-red-100 text-red-700 rounded-full">Sensitive (locked)</span>
                                @endif
                            </div>

                            <div class="mt-4">
                                @if ($item['redacted'])
                                    <p class="text-sm text-gray-500">Content hidden until unlock approval is active.</p>
                                @else
                                    @if ($item['body'])
                                        <p class="text-gray-800 whitespace-pre-wrap">{{ $item['body'] }}</p>
                                    @endif
                                    @if ($item['media_url'])
                                        <a href="{{ $item['media_url'] }}" target="_blank" class="text-indigo-600 underline text-sm">Open media ({{ $item['media_mime'] }})</a>
                                    @endif
                                @endif
                            </div>

                            <div class="mt-4">
                                <form method="POST" action="{{ route('vault.ui.upload', ['item' => $item['id']]) }}" enctype="multipart/form-data" class="flex items-center gap-3">
                                    @csrf
                                    <input type="file" name="media" class="text-sm" required>
                                    <button type="submit" class="px-3 py-1.5 bg-slate-700 text-white rounded-md text-sm">Upload media</button>
                                </form>
                            </div>

                            @if ($item['is_sensitive'])
                                <div class="mt-4 border-t pt-4">
                                    @php($pending = $pendingByItem[$item['id']] ?? null)

                                    @if (! $pending)
                                        <form method="POST" action="{{ route('vault.ui.requestUnlock', ['item' => $item['id']]) }}">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 bg-amber-600 text-white rounded-md text-sm">Request unlock</button>
                                        </form>
                                    @else
                                        <p class="text-sm text-amber-700 mb-2">
                                            Unlock request pending until {{ $pending->expires_at?->format('Y-m-d H:i') }}.
                                        </p>

                                        @if ((int) $pending->requested_by_user_id !== (int) $currentUserId)
                                            <div class="flex gap-2">
                                                <form method="POST" action="{{ route('vault.ui.approve', ['unlockRequest' => $pending->id]) }}">
                                                    @csrf
                                                    <button type="submit" class="px-3 py-1.5 bg-green-600 text-white rounded-md text-sm">Approve</button>
                                                </form>
                                                <form method="POST" action="{{ route('vault.ui.reject', ['unlockRequest' => $pending->id]) }}">
                                                    @csrf
                                                    <button type="submit" class="px-3 py-1.5 bg-red-600 text-white rounded-md text-sm">Reject</button>
                                                </form>
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="bg-white shadow-sm rounded-lg p-6 text-sm text-gray-600">
                            No vault items yet.
                        </div>
                    @endforelse
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
