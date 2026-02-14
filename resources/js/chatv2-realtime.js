function escapeHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function formatTime(value) {
    const date = value ? new Date(value) : new Date();
    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}

class ChatV2RealtimeClient {
    constructor(root) {
        this.root = root;
        this.conversationId = Number(root.dataset.conversationId);
        this.coupleId = Number(root.dataset.coupleId);
        this.userId = Number(root.dataset.userId);
        this.partnerName = root.dataset.partnerName || 'Partner';
        this.fetchUrl = root.dataset.routeFetch;
        this.sendUrl = root.dataset.routeSend;
        this.deliveredTemplate = root.dataset.routeDeliveredTemplate;
        this.readTemplate = root.dataset.routeReadTemplate;
        this.showDiagnostics = root.dataset.showDiagnostics === '1';
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
        this.messages = new Map();
        this.readMarked = new Set();
        this.partnerState = 'offline';
        this.partnerTyping = false;
        this.typingDebounce = null;
        this.typingIdleTimer = null;
        this.presenceChannel = null;
        this.privateChannel = null;
        this.lastEvent = null;

        this.listEl = root.querySelector('#chatv2-list');
        this.formEl = root.querySelector('#chatv2-form');
        this.inputEl = root.querySelector('#chatv2-input');
        this.sendEl = root.querySelector('#chatv2-send');
        this.statusEl = root.querySelector('#chatv2-partner-status');
        this.typingEl = root.querySelector('#chatv2-typing');
        this.refreshEl = root.querySelector('#chatv2-refresh');
        this.diagEl = root.querySelector('#chatv2-diagnostics');
        this.diagStatusEl = root.querySelector('#chatv2-diag-status');
        this.diagChannelEl = root.querySelector('#chatv2-diag-channel');
        this.diagLastEl = root.querySelector('#chatv2-diag-last');
    }

    async init() {
        this.setupDiagnostics();
        this.bindDomEvents();
        await this.loadInitialMessages();
        this.subscribeRealtime();
    }

    setupDiagnostics() {
        if (!this.showDiagnostics) {
            return;
        }

        this.diagEl.classList.remove('hidden');
        this.diagChannelEl.textContent = `private-conversation.${this.conversationId} + presence-couple.${this.coupleId}`;
    }

    setConnectionStatus(status) {
        if (this.showDiagnostics) {
            this.diagStatusEl.textContent = status;
        }
    }

    setLastEvent(label) {
        this.lastEvent = `${label} @ ${formatTime(new Date().toISOString())}`;
        if (this.showDiagnostics) {
            this.diagLastEl.textContent = this.lastEvent;
        }
    }

    bindDomEvents() {
        this.formEl.addEventListener('submit', async (event) => {
            event.preventDefault();
            await this.sendCurrentMessage();
        });

        this.inputEl.addEventListener('input', () => {
            this.handleTypingInput();
        });

        this.refreshEl.addEventListener('click', async () => {
            await this.loadInitialMessages();
        });

        window.addEventListener('focus', () => {
            this.markVisibleAsRead();
            this.whisperPresence(document.hidden ? 'away' : 'online');
        });

        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                this.markVisibleAsRead();
            }
            this.whisperPresence(document.hidden ? 'away' : 'online');
        });
    }

    async loadInitialMessages() {
        const response = await fetch(this.fetchUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                Accept: 'application/json',
            },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            return;
        }

        const payload = await response.json();
        this.messages.clear();
        this.listEl.innerHTML = '';
        for (const message of payload.messages ?? []) {
            this.upsertMessage(message);
            this.renderMessage(message);
        }

        this.scrollToBottom();
        this.markVisibleAsRead();
    }

    subscribeRealtime() {
        if (!window.Echo) {
            this.setConnectionStatus('echo-not-ready');
            return;
        }

        const connection = window.Echo.connector?.pusher?.connection;
        if (connection) {
            connection.bind('connected', () => this.setConnectionStatus('connected'));
            connection.bind('disconnected', () => this.setConnectionStatus('disconnected'));
        }

        this.privateChannel = window.Echo.private(`conversation.${this.conversationId}`)
            .listen('.chatv2.message.sent', async (event) => {
                this.setLastEvent('MessageSent');
                this.upsertMessage(event);
                this.renderMessage(event);
                this.scrollToBottom();

                if (event.sender_id !== this.userId) {
                    await this.markDelivered(event.id);
                    if (document.hasFocus() && !document.hidden) {
                        await this.markRead(event.id);
                    }
                }
            })
            .listen('.chatv2.message.delivered', (event) => {
                this.setLastEvent('MessageDelivered');
                this.applyReceiptUpdate(event.message_id, {
                    delivered_at: event.delivered_at,
                });
            })
            .listen('.chatv2.message.read', (event) => {
                this.setLastEvent('MessageRead');
                this.applyReceiptUpdate(event.message_id, {
                    delivered_at: event.read_at,
                    read_at: event.read_at,
                });
            });

        this.presenceChannel = window.Echo.join(`couple.${this.coupleId}`)
            .here((users) => {
                const partnerOnline = users.some((user) => Number(user.id) !== this.userId);
                this.partnerState = partnerOnline ? 'online' : 'offline';
                this.updateStatus();
            })
            .joining((user) => {
                if (Number(user.id) !== this.userId) {
                    this.partnerState = 'online';
                    this.updateStatus();
                }
            })
            .leaving((user) => {
                if (Number(user.id) !== this.userId) {
                    this.partnerState = 'offline';
                    this.partnerTyping = false;
                    this.updateStatus();
                }
            })
            .listenForWhisper('typing', (payload) => {
                if (Number(payload?.user_id) !== this.userId) {
                    this.partnerTyping = Boolean(payload?.typing);
                    this.updateStatus();
                }
            })
            .listenForWhisper('presence', (payload) => {
                if (Number(payload?.user_id) !== this.userId && payload?.state) {
                    this.partnerState = payload.state;
                    this.updateStatus();
                }
            });

        this.whisperPresence('online');
    }

    updateStatus() {
        this.statusEl.textContent = `${this.partnerName}: ${this.partnerState}`;
        this.typingEl.classList.toggle('hidden', !this.partnerTyping);
    }

    handleTypingInput() {
        if (!this.presenceChannel) {
            return;
        }

        clearTimeout(this.typingDebounce);
        this.typingDebounce = setTimeout(() => {
            this.presenceChannel.whisper('typing', {
                user_id: this.userId,
                typing: true,
            });
        }, 300);

        clearTimeout(this.typingIdleTimer);
        this.typingIdleTimer = setTimeout(() => {
            this.presenceChannel.whisper('typing', {
                user_id: this.userId,
                typing: false,
            });
        }, 2000);
    }

    whisperPresence(state) {
        if (!this.presenceChannel) {
            return;
        }

        this.presenceChannel.whisper('presence', {
            user_id: this.userId,
            state,
        });
    }

    async sendCurrentMessage() {
        const body = this.inputEl.value.trim();
        if (!body) {
            return;
        }

        this.sendEl.disabled = true;

        const response = await fetch(this.sendUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                Accept: 'application/json',
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                type: 'text',
                body,
            }),
        });

        this.sendEl.disabled = false;

        if (!response.ok) {
            return;
        }

        const payload = await response.json();
        const message = payload.message;
        this.inputEl.value = '';
        this.upsertMessage(message);
        this.renderMessage(message);
        this.scrollToBottom();

        if (this.presenceChannel) {
            this.presenceChannel.whisper('typing', {
                user_id: this.userId,
                typing: false,
            });
        }
    }

    upsertMessage(message) {
        const existing = this.messages.get(message.id) ?? {};
        this.messages.set(message.id, { ...existing, ...message });
    }

    renderMessage(message) {
        const existing = this.listEl.querySelector(`[data-message-id="${message.id}"]`);
        const model = this.messages.get(message.id) ?? message;
        const isMine = Number(model.sender_id) === this.userId;
        const receipts = model.receipts ?? [];
        const delivered = receipts.some((receipt) => Boolean(receipt.delivered_at));
        const read = receipts.some((receipt) => Boolean(receipt.read_at));
        const status = !isMine ? '' : read ? '✓✓ Read' : delivered ? '✓✓ Delivered' : '✓ Sent';

        const html = `
            <article class="mb-3 ${isMine ? 'text-right' : 'text-left'}" data-message-id="${model.id}">
                <div class="inline-block max-w-[85%] rounded-lg border px-3 py-2 ${isMine ? 'border-blue-200 bg-blue-50' : 'border-stone-200 bg-white'}">
                    <p class="whitespace-pre-wrap break-words text-sm text-stone-900">${escapeHtml(model.body ?? '')}</p>
                    <div class="mt-1 text-xs text-stone-500">${formatTime(model.created_at)} ${status}</div>
                </div>
            </article>
        `;

        if (existing) {
            existing.outerHTML = html;
        } else {
            this.listEl.insertAdjacentHTML('beforeend', html);
        }
    }

    applyReceiptUpdate(messageId, updates) {
        const model = this.messages.get(messageId);
        if (!model) {
            return;
        }

        const receipts = Array.isArray(model.receipts) ? [...model.receipts] : [];
        if (receipts.length === 0) {
            receipts.push({
                message_id: messageId,
                user_id: null,
                delivered_at: null,
                read_at: null,
            });
        }

        receipts[0] = { ...receipts[0], ...updates };
        model.receipts = receipts;
        this.messages.set(messageId, model);
        this.renderMessage(model);
    }

    async markDelivered(messageId) {
        const url = this.deliveredTemplate.replace('__ID__', String(messageId));
        await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': this.csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                Accept: 'application/json',
            },
            credentials: 'same-origin',
        });
    }

    async markRead(messageId) {
        if (this.readMarked.has(messageId)) {
            return;
        }

        this.readMarked.add(messageId);
        const url = this.readTemplate.replace('__ID__', String(messageId));
        await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': this.csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                Accept: 'application/json',
            },
            credentials: 'same-origin',
        });
    }

    markVisibleAsRead() {
        for (const message of this.messages.values()) {
            if (Number(message.sender_id) === this.userId) {
                continue;
            }

            const receipts = message.receipts ?? [];
            if (receipts.some((receipt) => receipt.read_at)) {
                continue;
            }

            this.markRead(message.id);
        }
    }

    scrollToBottom() {
        this.listEl.scrollTop = this.listEl.scrollHeight;
    }
}

export function bootChatV2Realtime() {
    const root = document.getElementById('chatv2-root');
    if (!root) {
        return;
    }

    const client = new ChatV2RealtimeClient(root);
    client.init();
}

