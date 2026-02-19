<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Chat</h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl px-4">
            @if (!$coupleId)
                <div class="rounded border border-amber-300 bg-amber-50 px-4 py-4 text-amber-900">
                    <p class="font-semibold">No couple selected.</p>
                    <a href="{{ url('/couple') }}" class="mt-2 inline-block underline">Go to Couple Linking</a>
                </div>
            @else
                <div
                    id="chat-root"
                    data-chat-realtime="1"
                    data-couple-id="{{ $coupleId }}"
                    data-user-id="{{ $currentUserId }}"
                    class="space-y-4"
                >
                    <div id="chat-errors" class="hidden rounded border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-800"></div>

                    <div id="chat-messages" class="h-[420px] overflow-y-auto rounded border bg-white p-3 space-y-2"></div>

                    <div class="flex items-center justify-between text-sm text-gray-600">
                        <div id="chat-typing" class="h-5 italic text-indigo-600"></div>
                        <div id="chat-seen" class="h-5 text-gray-500"></div>
                    </div>

                    <form id="chat-form" class="space-y-2">
                        @csrf
                        <textarea
                            id="chat-input"
                            rows="2"
                            maxlength="2000"
                            placeholder="Type a message..."
                            class="w-full rounded border-gray-300"
                        ></textarea>
                        <div class="flex justify-end">
                            <button id="chat-send" type="submit" class="rounded bg-indigo-600 px-4 py-2 text-white">
                                Send
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
