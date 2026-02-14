<x-app-layout>
    <div class="mx-auto max-w-4xl px-4 py-6">
        <div
            id="chatv2-root"
            class="rounded-lg border border-stone-300 bg-white shadow-sm"
            data-conversation-id="{{ $conversationId }}"
            data-couple-id="{{ $coupleId }}"
            data-user-id="{{ $userId }}"
            data-partner-name="{{ $partnerName }}"
            data-route-fetch="{{ route('chatv2.conversation.index') }}"
            data-route-send="{{ route('chatv2.messages.send') }}"
            data-route-delivered-template="{{ route('chatv2.messages.delivered', ['message' => '__ID__']) }}"
            data-route-read-template="{{ route('chatv2.messages.read', ['message' => '__ID__']) }}"
            data-show-diagnostics="{{ $showDiagnostics ? '1' : '0' }}"
        >
            <header class="flex items-center justify-between border-b border-stone-200 px-4 py-3">
                <div>
                    <h1 class="text-lg font-semibold text-stone-900">ChatV2</h1>
                    <p id="chatv2-partner-status" class="text-sm text-stone-500">Connecting...</p>
                </div>
                <button
                    id="chatv2-refresh"
                    type="button"
                    class="rounded border border-stone-300 px-3 py-1 text-sm text-stone-700 hover:bg-stone-50"
                >
                    Refresh
                </button>
            </header>

            <div id="chatv2-typing" class="hidden border-b border-stone-100 px-4 py-2 text-sm text-stone-600">
                Typing...
            </div>

            <main id="chatv2-list" class="h-[460px] overflow-y-auto bg-stone-50 px-4 py-3"></main>

            <footer class="border-t border-stone-200 p-3">
                <form id="chatv2-form" class="flex gap-2">
                    <input
                        id="chatv2-input"
                        type="text"
                        maxlength="5000"
                        autocomplete="off"
                        placeholder="Type a message..."
                        class="w-full rounded border border-stone-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none"
                    >
                    <button
                        id="chatv2-send"
                        type="submit"
                        class="rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-500"
                    >
                        Send
                    </button>
                </form>
            </footer>

            <aside
                id="chatv2-diagnostics"
                class="hidden border-t border-amber-200 bg-amber-50 px-4 py-2 text-xs text-amber-900"
            >
                <div>Status: <span id="chatv2-diag-status">disconnected</span></div>
                <div>Channel: <span id="chatv2-diag-channel">-</span></div>
                <div>Last event: <span id="chatv2-diag-last">-</span></div>
            </aside>
        </div>
    </div>
</x-app-layout>
