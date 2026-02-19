function csrfToken() {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    return token || '';
}

function formatDate(value) {
    if (!value) return '';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return '';
    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}

export function initChatRealtime() {
    const root = document.querySelector('#chat-root[data-chat-realtime="1"]');
    if (!root) {
        return;
    }

    const coupleId = Number(root.dataset.coupleId || 0);
    const currentUserId = Number(root.dataset.userId || 0);
    if (!coupleId) {
        return;
    }

    const messagesEl = document.getElementById('chat-messages');
    const typingEl = document.getElementById('chat-typing');
    const seenEl = document.getElementById('chat-seen');
    const errorsEl = document.getElementById('chat-errors');
    const formEl = document.getElementById('chat-form');
    const inputEl = document.getElementById('chat-input');
    const sendButtonEl = document.getElementById('chat-send');

    if (!messagesEl || !typingEl || !seenEl || !errorsEl || !formEl || !inputEl || !sendButtonEl) {
        return;
    }

    const state = {
        chatId: null,
        partnerReadMessageId: null,
        partnerReadAt: null,
        messages: new Map(),
        latestVisibleId: null,
        typingTimer: null,
        readDebounceTimer: null,
        typingLastSentAt: 0,
        isSending: false,
    };

    function isNearBottom() {
        return messagesEl.scrollHeight - messagesEl.scrollTop - messagesEl.clientHeight < 100;
    }

    function updateSendState() {
        const hasText = Boolean(inputEl.value.trim());
        sendButtonEl.disabled = state.isSending || !hasText;
        sendButtonEl.textContent = state.isSending ? 'Sending...' : 'Send';
    }

    function showError(message) {
        errorsEl.textContent = message;
        errorsEl.classList.remove('hidden');
    }

    function clearError() {
        errorsEl.textContent = '';
        errorsEl.classList.add('hidden');
    }

    function setTypingVisible(visible) {
        typingEl.textContent = visible ? 'Partner is typing...' : '';
    }

    function renderSeen() {
        if (!state.partnerReadMessageId) {
            seenEl.textContent = '';
            return;
        }

        const ownMessages = [...state.messages.values()].filter((m) => m.sender_id === currentUserId && !m.deleted);
        if (ownMessages.length === 0) {
            seenEl.textContent = '';
            return;
        }

        const latestOwn = ownMessages.sort((a, b) => b.id - a.id)[0];
        if ((state.partnerReadMessageId || 0) >= latestOwn.id) {
            const at = formatDate(state.partnerReadAt);
            seenEl.textContent = at ? `Seen ${at}` : 'Seen';
        } else {
            seenEl.textContent = '';
        }
    }

    function renderMessages() {
        const shouldStickToBottom = isNearBottom();
        const sorted = [...state.messages.values()].sort((a, b) => a.id - b.id);
        messagesEl.innerHTML = '';

        sorted.forEach((message) => {
            const isOwn = message.sender_id === currentUserId;
            const wrapper = document.createElement('div');
            wrapper.className = `rounded-2xl border px-3 py-2 shadow-sm ${
                isOwn
                    ? 'ml-8 border-pink-200 bg-rose-50 text-slate-900'
                    : 'mr-8 border-amber-200 bg-amber-50 text-slate-900'
            }`;
            wrapper.dataset.messageId = String(message.id);

            const body = message.deleted
                ? '<span class="italic text-gray-500">Message deleted</span>'
                : (message.body || '[Attachment]');

            wrapper.innerHTML = `
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">${isOwn ? 'You' : (message.sender_name || 'Partner')}</div>
                        <div class="text-sm break-words">${body}</div>
                        <div class="text-[11px] text-slate-400 mt-1">${formatDate(message.sent_at)}</div>
                    </div>
                    ${isOwn && !message.deleted ? `<button class="chat-delete text-xs text-red-600 underline" data-delete-id="${message.id}" type="button">Delete</button>` : ''}
                </div>
            `;

            messagesEl.appendChild(wrapper);
        });

        state.latestVisibleId = sorted.length ? sorted[sorted.length - 1].id : null;
        if (shouldStickToBottom) {
            messagesEl.scrollTop = messagesEl.scrollHeight;
        }
        renderSeen();
    }

    function upsertMessage(message) {
        if (!message || !message.id) {
            return;
        }

        const previous = state.messages.get(message.id) || {};
        state.messages.set(message.id, { ...previous, ...message });
        renderMessages();
        queueMarkRead();
    }

    function markDeleted(messageId) {
        const existing = state.messages.get(messageId);
        if (!existing) {
            return;
        }
        state.messages.set(messageId, { ...existing, deleted: true, body: null });
        renderMessages();
    }

    async function api(url, options = {}) {
        const response = await fetch(url, {
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                ...(options.headers || {}),
            },
            credentials: 'same-origin',
            ...options,
        });

        if (!response.ok) {
            throw new Error(`Request failed (${response.status})`);
        }

        if (response.status === 204) {
            return null;
        }

        return response.json();
    }

    async function loadThread() {
        const thread = await api('/chat-v1');
        state.chatId = thread.chat_id;

        const partner = (thread.participants || []).find((p) => Number(p.user_id) !== currentUserId);
        state.partnerReadMessageId = partner?.last_read_message_id || null;
        state.partnerReadAt = partner?.last_read_at || null;
        renderSeen();
    }

    async function loadMessages() {
        const payload = await api('/chat-v1/messages?limit=30');
        const list = payload?.messages || [];
        list.reverse().forEach((message) => {
            state.messages.set(message.id, { ...message, deleted: false });
        });
        renderMessages();
        queueMarkRead();
    }

    async function sendMessage(body) {
        const payload = await api('/chat-v1/messages', {
            method: 'POST',
            body: JSON.stringify({ type: 'text', body }),
        });

        if (payload?.message) {
            upsertMessage({ ...payload.message, deleted: false });
        }
    }

    async function deleteMessage(messageId) {
        await api(`/chat-v1/messages/${messageId}`, { method: 'DELETE' });
        markDeleted(messageId);
    }

    async function markRead() {
        if (!state.latestVisibleId) {
            return;
        }
        await api('/chat-v1/read', {
            method: 'POST',
            body: JSON.stringify({ last_read_message_id: state.latestVisibleId }),
        });
    }

    function queueMarkRead() {
        if (state.readDebounceTimer) {
            clearTimeout(state.readDebounceTimer);
        }
        state.readDebounceTimer = setTimeout(() => {
            markRead().catch(() => {});
        }, 500);
    }

    function bindDeleteButtons() {
        messagesEl.addEventListener('click', (event) => {
            const target = event.target;
            if (!(target instanceof HTMLElement)) return;

            const id = target.dataset.deleteId;
            if (!id) return;

            deleteMessage(Number(id)).catch(() => {
                showError('Could not delete message.');
            });
        });
    }

    function bindTyping(channel) {
        inputEl.addEventListener('keydown', () => {
            const now = Date.now();
            if (now - state.typingLastSentAt < 1000) {
                return;
            }
            state.typingLastSentAt = now;
            channel.whisper('typing', { user_id: currentUserId || null, is_typing: true });
        });
    }

    function setupRealtime() {
        if (!window.Echo) {
            showError('Realtime client is not configured.');
            return;
        }

        const channel = window.Echo.private(`couple.${coupleId}`);

        channel.listen('.chat.message.sent', (event) => {
            if (event?.message) {
                upsertMessage({ ...event.message, deleted: false });
            }
        });

        channel.listen('.chat.message.deleted', (event) => {
            if (event?.message_id) {
                markDeleted(Number(event.message_id));
            }
        });

        channel.listen('.chat.read.updated', (event) => {
            if (Number(event?.user_id) === currentUserId) {
                return;
            }
            state.partnerReadMessageId = event?.last_read_message_id || null;
            state.partnerReadAt = event?.last_read_at || null;
            renderSeen();
        });

        channel.listenForWhisper('typing', (event) => {
            if (Number(event?.user_id) === currentUserId) {
                return;
            }

            setTypingVisible(Boolean(event?.is_typing));
            if (state.typingTimer) {
                clearTimeout(state.typingTimer);
            }
            state.typingTimer = setTimeout(() => setTypingVisible(false), 3000);
        });

        bindTyping(channel);
    }

    formEl.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearError();

        const body = inputEl.value.trim();
        if (!body) {
            updateSendState();
            return;
        }

        try {
            state.isSending = true;
            updateSendState();
            await sendMessage(body);
            inputEl.value = '';
        } catch (error) {
            showError('Could not send message.');
        } finally {
            state.isSending = false;
            updateSendState();
        }
    });

    inputEl.addEventListener('input', updateSendState);

    Promise.all([loadThread(), loadMessages()])
        .then(() => {
            clearError();
            bindDeleteButtons();
            setupRealtime();
            updateSendState();
        })
        .catch(() => {
            showError('Unable to load chat.');
            updateSendState();
        });
}
