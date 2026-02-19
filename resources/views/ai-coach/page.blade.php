<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            AI Coach
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
                <div id="ai-coach-root"
                    data-couple-id="{{ $coupleId }}"
                    data-session-id="{{ $currentSession?->id }}"
                    class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <section class="lg:col-span-1 bg-white shadow-sm sm:rounded-lg p-4">
                        <h3 class="font-semibold text-gray-900 mb-3">Sessions</h3>
                        <div class="mb-4 grid grid-cols-3 gap-2">
                            @foreach (['vent' => 'Vent', 'bridge' => 'Bridge', 'repair' => 'Repair'] as $mode => $label)
                                <form method="POST" action="{{ route('ai.coach.session.create') }}">
                                    @csrf
                                    <input type="hidden" name="mode" value="{{ $mode }}">
                                    <button type="submit" class="w-full text-xs px-2 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">
                                        {{ $label }}
                                    </button>
                                </form>
                            @endforeach
                        </div>
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
                        @if (! $currentSession)
                            <p class="text-sm text-gray-500">Select or create a session to start.</p>
                        @else
                            <div id="ai-session-closed" class="hidden mb-3 px-3 py-2 rounded border border-yellow-300 bg-yellow-50 text-yellow-800 text-sm">
                                Session closed
                            </div>

                            <div id="ai-messages-stream" class="space-y-2 border rounded p-3 h-80 overflow-y-auto bg-gray-50">
                                @foreach ($messages as $message)
                                    <div class="rounded px-3 py-2 text-sm {{ $message->sender_type === 'ai' ? 'bg-blue-50 border border-blue-100' : 'bg-white border border-gray-200' }}">
                                        <div class="font-semibold text-xs uppercase text-gray-500">{{ $message->sender_type }}</div>
                                        <div class="text-gray-900 whitespace-pre-wrap">{{ $message->content }}</div>
                                    </div>
                                @endforeach
                            </div>

                            <form id="ai-send-form" class="mt-4" method="POST" action="{{ route('ai.coach.send', ['session' => $currentSession->id]) }}">
                                @csrf
                                <label for="content" class="block text-sm font-medium text-gray-700 mb-1">Your message</label>
                                <textarea id="content" name="content" rows="4" required class="w-full border-gray-300 rounded-md shadow-sm"></textarea>
                                <button type="submit" class="mt-2 px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">
                                    Send
                                </button>
                            </form>

                            <div id="ai-thinking-indicator" class="hidden mt-3 text-sm text-indigo-700">
                                Coach is thinking...
                            </div>

                            <div id="ai-draft-panel" class="mt-4 border rounded p-3 bg-white">
                                @if ($draft)
                                    <h4 class="font-semibold text-gray-900">Draft</h4>
                                    <p class="text-xs text-gray-500 uppercase">{{ $draft->draft_type }}</p>
                                    <p class="mt-2 text-sm whitespace-pre-wrap">{{ $draft->content }}</p>
                                    <div class="mt-3 flex gap-2">
                                        <form method="POST" action="{{ route('ai.coach.draft.accept', ['session' => $currentSession->id, 'draft' => $draft->id]) }}">
                                            @csrf
                                            <button type="submit" class="px-3 py-2 text-sm rounded bg-green-600 text-white hover:bg-green-700">Accept</button>
                                        </form>
                                        <form method="POST" action="{{ route('ai.coach.draft.discard', ['session' => $currentSession->id, 'draft' => $draft->id]) }}">
                                            @csrf
                                            <button type="submit" class="px-3 py-2 text-sm rounded bg-gray-700 text-white hover:bg-gray-800">Discard</button>
                                        </form>
                                    </div>
                                @else
                                    <p class="text-sm text-gray-500">No active draft.</p>
                                @endif
                            </div>
                        @endif
                    </section>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
