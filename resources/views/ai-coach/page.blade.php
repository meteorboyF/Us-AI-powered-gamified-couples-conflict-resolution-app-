<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            AI Coach
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded border border-emerald-200 bg-emerald-50 px-4 py-2 text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 rounded border border-red-200 bg-red-50 px-4 py-2 text-red-800">
                    {{ $errors->first() }}
                </div>
            @endif

            @if (! $coupleId)
                <div class="rounded-lg border border-amber-300 bg-amber-50 p-6">
                    <p class="font-semibold text-amber-900">No couple selected</p>
                    <a href="/couple" class="mt-2 inline-block text-amber-900 underline">Go to couple setup</a>
                </div>
            @else
                <div
                    id="ai-coach-root"
                    data-couple-id="{{ $coupleId }}"
                    data-session-id="{{ $currentSession?->id }}"
                    data-accept-url-template="{{ route('ai.coach.draft.accept', ['session' => $currentSession?->id ?: 0, 'draft' => '__DRAFT__']) }}"
                    data-discard-url-template="{{ route('ai.coach.draft.discard', ['session' => $currentSession?->id ?: 0, 'draft' => '__DRAFT__']) }}"
                    class="grid grid-cols-1 gap-6 lg:grid-cols-12"
                >
                    <aside class="rounded-xl border border-slate-200 bg-slate-50 p-4 lg:col-span-4">
                        <h3 class="text-lg font-semibold text-slate-900">Coach Room</h3>
                        <p class="mt-1 text-xs text-slate-600">Create a session and choose where to focus.</p>

                        <form method="POST" action="{{ route('ai.coach.session.create') }}" class="mt-4 space-y-3 rounded-lg border border-slate-200 bg-white p-3">
                            @csrf
                            <div>
                                <label for="mode" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Mode</label>
                                <select id="mode" name="mode" class="w-full rounded border-slate-300 text-sm">
                                    <option value="vent">Vent</option>
                                    <option value="bridge">Bridge</option>
                                    <option value="repair">Repair</option>
                                </select>
                            </div>
                            <div>
                                <label for="title" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Title (optional)</label>
                                <input id="title" name="title" type="text" class="w-full rounded border-slate-300 text-sm" placeholder="Tonight check-in" />
                            </div>
                            <button type="submit" class="w-full rounded bg-slate-900 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-700">
                                Create Session
                            </button>
                        </form>

                        <div class="mt-4 space-y-2">
                            @forelse ($sessions as $session)
                                <a
                                    href="{{ route('ai.coach.page', ['session' => $session->id]) }}"
                                    class="block rounded-lg border px-3 py-2 text-sm {{ $currentSession?->id === $session->id ? 'border-sky-400 bg-sky-50' : 'border-slate-200 bg-white' }}"
                                >
                                    <div class="flex items-center justify-between">
                                        <span class="font-semibold uppercase tracking-wide text-slate-800">{{ $session->mode }}</span>
                                        <span class="text-xs text-slate-500">#{{ $session->id }}</span>
                                    </div>
                                    <div class="mt-1 text-xs text-slate-500">{{ $session->status }}</div>
                                </a>
                            @empty
                                <p class="rounded border border-dashed border-slate-300 p-3 text-sm text-slate-500">No sessions yet.</p>
                            @endforelse
                        </div>
                    </aside>

                    <section class="rounded-xl border border-slate-200 bg-white p-4 lg:col-span-8">
                        @if (! $currentSession)
                            <p class="text-sm text-slate-500">Select or create a session to start.</p>
                        @else
                            <div id="ai-session-closed" class="mb-3 hidden rounded border border-amber-300 bg-amber-50 px-3 py-2 text-sm text-amber-800">
                                Session closed
                            </div>

                            @if (! empty($currentSession->safety_flags))
                                <div class="mb-3 rounded border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-900">
                                    Safety note: Sensitive context detected. Responses are guidance-only and never auto-sent.
                                </div>
                            @endif

                            <div class="mb-2 text-xs text-slate-600">Nothing is sent automatically. You always review before sending.</div>

                            <div id="ai-messages-stream" class="h-80 space-y-3 overflow-y-auto rounded-lg border border-slate-200 bg-slate-50 p-3">
                                @foreach ($messages as $message)
                                    <div class="ai-message rounded-lg border px-3 py-2 text-sm {{ $message->sender_type === 'ai' ? 'border-sky-200 bg-sky-50' : 'border-slate-200 bg-white' }}">
                                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $message->sender_type }}</div>
                                        <div class="whitespace-pre-wrap text-slate-900">{{ $message->content }}</div>
                                    </div>
                                @endforeach
                            </div>

                            <form id="ai-send-form" class="mt-4" method="POST" action="{{ route('ai.coach.send', ['session' => $currentSession->id]) }}">
                                @csrf
                                <label for="content" class="mb-1 block text-sm font-medium text-slate-700">Your message</label>
                                <textarea id="content" name="content" rows="4" required class="w-full rounded border-slate-300" placeholder="Share what happened and what outcome you want."></textarea>
                                <div class="mt-2 flex items-center justify-between">
                                    <div id="ai-thinking-indicator" class="hidden text-sm text-sky-700">Coach is thinking...</div>
                                    <button id="ai-send-button" type="submit" class="rounded bg-slate-900 px-4 py-2 text-white hover:bg-slate-700 disabled:cursor-not-allowed disabled:opacity-60">
                                        Send
                                    </button>
                                </div>
                            </form>

                            <div id="ai-draft-panel" class="mt-4 rounded-lg border border-slate-200 bg-slate-50 p-3">
                                @if ($draft)
                                    <div class="mb-1 text-sm font-semibold text-slate-900">Latest Draft</div>
                                    <p class="text-xs uppercase tracking-wide text-slate-500">{{ $draft->draft_type }}</p>
                                    <p class="mt-2 whitespace-pre-wrap text-sm text-slate-800">{{ $draft->content }}</p>
                                    <div class="mt-3 flex gap-2">
                                        <form method="POST" action="{{ route('ai.coach.draft.accept', ['session' => $currentSession->id, 'draft' => $draft->id]) }}">
                                            @csrf
                                            <button type="submit" class="rounded bg-emerald-600 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Accept</button>
                                        </form>
                                        <form method="POST" action="{{ route('ai.coach.draft.discard', ['session' => $currentSession->id, 'draft' => $draft->id]) }}">
                                            @csrf
                                            <button type="submit" class="rounded bg-slate-700 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-800">Discard</button>
                                        </form>
                                    </div>
                                @else
                                    <p class="text-sm text-slate-500">No active draft.</p>
                                @endif
                            </div>
                        @endif
                    </section>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
