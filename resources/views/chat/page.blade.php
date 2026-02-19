<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Chat</h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-4xl px-4">
            @if (! $coupleId)
                <div class="rounded border border-amber-300 bg-amber-50 px-4 py-4 text-amber-900">
                    <p class="font-semibold">No couple selected.</p>
                    <a href="{{ url('/couple') }}" class="mt-2 inline-block underline">Go to Couple Linking</a>
                </div>
            @else
                <div class="overflow-hidden rounded-2xl border-4 border-amber-200 bg-slate-900 shadow-xl">
                    <header class="border-b-4 border-amber-200 bg-slate-800 px-5 py-4 text-amber-100">
                        <div class="flex items-center justify-between">
                            <a href="/dashboard-ui" class="text-xs uppercase tracking-wide text-amber-200 hover:text-white">Back</a>
                            <div class="text-center">
                                <p class="text-sm font-semibold">Cozy Chat</p>
                                <p class="text-[11px] text-emerald-300">Connected</p>
                            </div>
                            <a href="/app" class="text-xs uppercase tracking-wide text-amber-200 hover:text-white">Hub</a>
                        </div>
                    </header>

                    <div
                        id="chat-root"
                        data-chat-realtime="1"
                        data-couple-id="{{ $coupleId }}"
                        data-user-id="{{ $currentUserId }}"
                        class="space-y-4 bg-gradient-to-b from-slate-900 to-slate-800 p-4"
                    >
                        <div id="chat-errors" class="hidden rounded border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-800"></div>

                        <div id="chat-messages" class="h-[420px] overflow-y-auto rounded-xl border border-amber-100/20 bg-slate-950/50 p-3"></div>

                        <div class="flex items-center justify-between text-sm">
                            <div id="chat-typing" class="h-5 italic text-emerald-300"></div>
                            <div id="chat-seen" class="h-5 text-amber-200"></div>
                        </div>

                        <form id="chat-form" class="space-y-2">
                            @csrf
                            <textarea
                                id="chat-input"
                                rows="2"
                                maxlength="2000"
                                placeholder="Type a message..."
                                class="w-full rounded-xl border-2 border-amber-200/80 bg-amber-50 px-3 py-2 text-slate-900 placeholder:text-slate-500 focus:border-pink-300 focus:ring-0"
                            ></textarea>
                            <div class="flex justify-end">
                                <button
                                    id="chat-send"
                                    type="submit"
                                    class="rounded-lg border-b-4 border-pink-800 bg-pink-600 px-5 py-2 text-sm font-semibold uppercase tracking-wide text-white transition hover:bg-pink-500 disabled:cursor-not-allowed disabled:opacity-60"
                                >
                                    Send
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
