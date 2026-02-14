<x-app-layout>
    <div class="chatv2-scene px-4 py-6">
        <div
            id="chatv2-root"
            class="mx-auto w-full max-w-5xl"
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
            <x-chatv2-pixel.panel tone="header" class="mb-3 p-3">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="chatv2-avatar">{{ strtoupper(mb_substr($partnerName, 0, 1)) }}</div>
                        <div>
                            <p class="chatv2-name">{{ $partnerName }}</p>
                            <div class="flex items-center gap-2">
                                <x-chatv2-pixel.badge id="chatv2-presence-badge" state="offline">
                                    Offline
                                </x-chatv2-pixel.badge>
                                <p id="chatv2-partner-status" class="chatv2-subtle">Connecting...</p>
                            </div>
                        </div>
                    </div>

                    <x-chatv2-pixel.button id="chatv2-refresh" variant="ghost" size="sm" type="button">
                        Sync
                    </x-chatv2-pixel.button>
                </div>

                <div id="chatv2-typing" class="chatv2-typing hidden mt-2">
                    {{ $partnerName }} is typing...
                </div>
            </x-chatv2-pixel.panel>

            <x-chatv2-pixel.panel class="relative p-3">
                <button id="chatv2-load-older" type="button" class="chatv2-load-older hidden">
                    Load older messages
                </button>

                <main id="chatv2-list" class="chatv2-list h-[56vh] overflow-y-auto px-2 py-3"></main>

                <x-chatv2-pixel.toast id="chatv2-new-pill" class="chatv2-new-pill hidden cursor-pointer">
                    New messages
                </x-chatv2-pixel.toast>
            </x-chatv2-pixel.panel>

            <x-chatv2-pixel.panel tone="composer" class="mt-3 p-3">
                <form id="chatv2-form" class="flex items-center gap-2">
                    <x-chatv2-pixel.button type="button" variant="ghost" size="sm" title="Attach">
                        <x-chatv2-pixel.icon name="attach" />
                    </x-chatv2-pixel.button>

                    <x-chatv2-pixel.button type="button" variant="ghost" size="sm" title="Voice">
                        <x-chatv2-pixel.icon name="mic" />
                    </x-chatv2-pixel.button>

                    <input
                        id="chatv2-input"
                        type="text"
                        maxlength="5000"
                        autocomplete="off"
                        placeholder="Send a kind message..."
                        class="chatv2-input"
                    >

                    <x-chatv2-pixel.button id="chatv2-send" type="submit" variant="primary">
                        <x-chatv2-pixel.icon name="send" /> Send
                    </x-chatv2-pixel.button>
                </form>
            </x-chatv2-pixel.panel>

            <aside id="chatv2-diagnostics" class="hidden mt-3">
                <x-chatv2-pixel.toast tone="warn" class="text-xs">
                    <div>Status: <span id="chatv2-diag-status">disconnected</span></div>
                    <div>Channel: <span id="chatv2-diag-channel">-</span></div>
                    <div>Last event: <span id="chatv2-diag-last">-</span></div>
                </x-chatv2-pixel.toast>
            </aside>
        </div>
    </div>

    <style>
        .chatv2-scene {
            --chatv2-bg: radial-gradient(circle at top, #3f4a59 0%, #252933 60%, #1f222a 100%);
            --chatv2-panel: #f0dfc3;
            --chatv2-panel-deep: #e5cda3;
            --chatv2-ink: #2f2318;
            --chatv2-accent: #d1885a;
            --chatv2-accent-press: #a25f3a;
            --chatv2-other: #f8ecd6;
            --chatv2-self: #dbeef7;
            --chatv2-border: #3e2c1f;
            min-height: calc(100vh - 5rem);
            background: var(--chatv2-bg);
            border-radius: 16px;
        }

        .chatv2-panel {
            border: 3px solid var(--chatv2-border);
            background: linear-gradient(180deg, var(--chatv2-panel), var(--chatv2-panel-deep));
            border-radius: 12px;
            box-shadow: 0 5px 0 #2a1f16;
        }

        .chatv2-panel-header {
            background: linear-gradient(180deg, #f3e3c8, #e2c396);
        }

        .chatv2-panel-composer {
            background: linear-gradient(180deg, #f2e2c7, #d9bb8f);
        }

        .chatv2-name {
            color: var(--chatv2-ink);
            font-weight: 700;
            letter-spacing: 0.02em;
        }

        .chatv2-subtle {
            color: #5f4a37;
            font-size: 0.8rem;
        }

        .chatv2-avatar {
            width: 2.5rem;
            height: 2.5rem;
            display: grid;
            place-items: center;
            font-weight: 700;
            color: #faf6ef;
            border: 2px solid var(--chatv2-border);
            border-radius: 8px;
            background: linear-gradient(180deg, #607791, #3f546c);
        }

        .chatv2-btn {
            border: 2px solid var(--chatv2-border);
            border-radius: 8px;
            font-weight: 700;
            letter-spacing: 0.02em;
            transition: transform 0.06s ease;
        }

        .chatv2-btn:active {
            transform: translateY(2px);
        }

        .chatv2-btn-primary {
            color: #fff5e8;
            background: linear-gradient(180deg, var(--chatv2-accent), #b96f44);
            box-shadow: 0 3px 0 var(--chatv2-accent-press);
        }

        .chatv2-btn-ghost {
            color: var(--chatv2-ink);
            background: linear-gradient(180deg, #f5e7ce, #e4c99f);
            box-shadow: 0 3px 0 #b0875e;
        }

        .chatv2-btn-danger {
            color: #fff;
            background: linear-gradient(180deg, #dc5b5b, #b64444);
            box-shadow: 0 3px 0 #863030;
        }

        .chatv2-btn-sm {
            font-size: 0.75rem;
            padding: 0.35rem 0.6rem;
        }

        .chatv2-btn-md {
            font-size: 0.82rem;
            padding: 0.42rem 0.8rem;
        }

        .chatv2-btn-lg {
            font-size: 0.9rem;
            padding: 0.55rem 1rem;
        }

        .chatv2-badge {
            border: 2px solid var(--chatv2-border);
            border-radius: 999px;
            padding: 0.1rem 0.55rem;
            color: #fff;
            font-size: 0.72rem;
        }

        .chatv2-badge-online {
            background: #3b8f5b;
        }

        .chatv2-badge-away {
            background: #b68f3b;
        }

        .chatv2-badge-offline {
            background: #6b7480;
        }

        .chatv2-toast {
            border: 2px solid #4d3523;
            background: #fbe5bc;
            border-radius: 10px;
            padding: 0.4rem 0.7rem;
            color: #452f1e;
        }

        .chatv2-toast-warn {
            background: #ffe4b5;
        }

        .chatv2-toast-success {
            background: #d7f1dd;
        }

        .chatv2-typing {
            color: #5f432d;
            font-size: 0.8rem;
            font-style: italic;
        }

        .chatv2-list {
            scrollbar-width: thin;
            scrollbar-color: #9f7f5e #e8d5b8;
        }

        .chatv2-message {
            display: flex;
            margin-bottom: 0.65rem;
        }

        .chatv2-message.mine {
            justify-content: flex-end;
        }

        .chatv2-message.other {
            justify-content: flex-start;
        }

        .chatv2-bubble {
            max-width: 80%;
            border: 2px solid var(--chatv2-border);
            border-radius: 12px;
            padding: 0.55rem 0.7rem;
            color: #2e241d;
            box-shadow: 0 3px 0 #8f6d4f;
        }

        .chatv2-bubble.mine {
            background: var(--chatv2-self);
            border-bottom-right-radius: 4px;
        }

        .chatv2-bubble.other {
            background: var(--chatv2-other);
            border-bottom-left-radius: 4px;
        }

        .chatv2-meta {
            margin-top: 0.25rem;
            font-size: 0.72rem;
            color: #654c38;
            display: flex;
            gap: 0.45rem;
            justify-content: flex-end;
            align-items: center;
        }

        .chatv2-meta.other {
            justify-content: flex-start;
        }

        .chatv2-status-tick {
            font-weight: 700;
        }

        .chatv2-status-read {
            color: #2e7a52;
        }

        .chatv2-input {
            width: 100%;
            border: 2px solid var(--chatv2-border);
            border-radius: 8px;
            padding: 0.45rem 0.65rem;
            background: #fff9ee;
            color: #2f2318;
        }

        .chatv2-input:focus {
            outline: none;
            border-color: #745844;
        }

        .chatv2-icon,
        .chatv2-icon-fallback {
            width: 0.9rem;
            height: 0.9rem;
            display: inline-grid;
            place-items: center;
        }

        .chatv2-load-older {
            width: 100%;
            border: 2px solid var(--chatv2-border);
            border-radius: 8px;
            background: #f7e7cd;
            color: #412f21;
            padding: 0.35rem;
            margin-bottom: 0.6rem;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .chatv2-new-pill {
            position: absolute;
            left: 50%;
            bottom: 0.8rem;
            transform: translateX(-50%);
            z-index: 15;
        }
    </style>
</x-app-layout>

