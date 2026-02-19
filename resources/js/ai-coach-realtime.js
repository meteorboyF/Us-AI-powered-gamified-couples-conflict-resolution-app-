const toInt = (value) => {
    const parsed = Number.parseInt(String(value ?? ''), 10);

    return Number.isNaN(parsed) ? null : parsed;
};

const isNearBottom = (element) => element.scrollHeight - element.scrollTop - element.clientHeight < 100;

const appendMessage = (message) => {
    const stream = document.getElementById('ai-messages-stream');
    if (!stream) {
        return;
    }

    const shouldStick = isNearBottom(stream);
    const wrapper = document.createElement('div');
    wrapper.className = `ai-message rounded-lg border px-3 py-2 text-sm ${
        message.sender_type === 'ai' ? 'border-sky-200 bg-sky-50' : 'border-slate-200 bg-white'
    }`;

    const sender = document.createElement('div');
    sender.className = 'text-xs font-semibold uppercase tracking-wide text-slate-500';
    sender.textContent = message.sender_type ?? 'unknown';

    const content = document.createElement('div');
    content.className = 'whitespace-pre-wrap text-slate-900';
    content.textContent = message.content ?? '';

    wrapper.appendChild(sender);
    wrapper.appendChild(content);
    stream.appendChild(wrapper);

    if (shouldStick) {
        stream.scrollTop = stream.scrollHeight;
    }
};

const renderDraft = (draft, sessionId) => {
    const panel = document.getElementById('ai-draft-panel');
    const root = document.getElementById('ai-coach-root');
    if (!panel || !root) {
        return;
    }

    if (!draft) {
        panel.innerHTML = '<p class="text-sm text-slate-500">No active draft.</p>';

        return;
    }

    const acceptTemplate = root.dataset.acceptUrlTemplate || '';
    const discardTemplate = root.dataset.discardUrlTemplate || '';
    const acceptUrl = acceptTemplate.replace('__DRAFT__', String(draft.id));
    const discardUrl = discardTemplate.replace('__DRAFT__', String(draft.id));

    panel.innerHTML = `
        <div class="mb-1 text-sm font-semibold text-slate-900">Latest Draft</div>
        <p class="text-xs uppercase tracking-wide text-slate-500">${draft.draft_type ?? ''}</p>
        <p class="mt-2 whitespace-pre-wrap text-sm text-slate-800">${draft.content ?? ''}</p>
        <div class="mt-3 flex gap-2">
            <form method="POST" action="${acceptUrl}">
                <input type="hidden" name="_token" value="${csrfToken()}">
                <button type="submit" class="rounded bg-emerald-600 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Accept</button>
            </form>
            <form method="POST" action="${discardUrl}">
                <input type="hidden" name="_token" value="${csrfToken()}">
                <button type="submit" class="rounded bg-slate-700 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-800">Discard</button>
            </form>
        </div>
    `;
};

const setThinking = (isThinking) => {
    window.__aiCoachThinking = isThinking;
    const indicator = document.getElementById('ai-thinking-indicator');
    if (!indicator) {
        return;
    }

    indicator.classList.toggle('hidden', !isThinking);
};

const setSendState = (isSending) => {
    const textarea = document.getElementById('content');
    const button = document.getElementById('ai-send-button');
    if (!textarea || !button) {
        return;
    }

    const hasText = Boolean(textarea.value.trim());
    button.disabled = isSending || !hasText;
    button.textContent = isSending ? 'Sending...' : 'Send';
};

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

export function initAiCoachRealtime({ coupleId, sessionId }) {
    const normalizedCoupleId = toInt(coupleId);
    const normalizedSessionId = toInt(sessionId);

    if (!normalizedCoupleId || !normalizedSessionId || !window.Echo) {
        return;
    }

    const form = document.getElementById('ai-send-form');
    const textarea = document.getElementById('content');

    let isSending = false;

    const updateSendState = () => setSendState(isSending);

    textarea?.addEventListener('input', updateSendState);
    updateSendState();

    if (form && textarea) {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            const content = textarea.value.trim();
            if (!content || isSending) {
                updateSendState();

                return;
            }

            isSending = true;
            setThinking(true);
            updateSendState();

            try {
                const response = await fetch(`/ai/sessions/${normalizedSessionId}/user-message`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrfToken(),
                    },
                    body: JSON.stringify({ content }),
                    credentials: 'same-origin',
                });

                if (response.ok) {
                    textarea.value = '';
                }
            } finally {
                isSending = false;
                updateSendState();
            }
        });
    }

    window.Echo.private(`couple.${normalizedCoupleId}`)
        .listen('.ai.session.message.created', (event) => {
            if (toInt(event?.session_id) !== normalizedSessionId || !event?.message) {
                return;
            }

            appendMessage(event.message);
            if (event.message.sender_type === 'ai') {
                setThinking(false);
            }
        })
        .listen('.ai.draft.created', (event) => {
            if (toInt(event?.session_id) !== normalizedSessionId) {
                return;
            }

            renderDraft(event.draft, normalizedSessionId);
        })
        .listen('.ai.session.closed', (event) => {
            if (toInt(event?.session_id) !== normalizedSessionId) {
                return;
            }

            const badge = document.getElementById('ai-session-closed');
            if (badge) {
                badge.classList.remove('hidden');
            }
        });
}
