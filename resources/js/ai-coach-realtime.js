const toInt = (value) => {
    const parsed = Number.parseInt(String(value ?? ''), 10);

    return Number.isNaN(parsed) ? null : parsed;
};

const appendMessage = (message) => {
    const stream = document.getElementById('ai-messages-stream');
    if (!stream) {
        return;
    }

    const wrapper = document.createElement('div');
    wrapper.className = `rounded px-3 py-2 text-sm ${message.sender_type === 'ai' ? 'bg-blue-50 border border-blue-100' : 'bg-white border border-gray-200'}`;

    const sender = document.createElement('div');
    sender.className = 'font-semibold text-xs uppercase text-gray-500';
    sender.textContent = message.sender_type ?? 'unknown';

    const content = document.createElement('div');
    content.className = 'text-gray-900 whitespace-pre-wrap';
    content.textContent = message.content ?? '';

    wrapper.appendChild(sender);
    wrapper.appendChild(content);
    stream.appendChild(wrapper);
    stream.scrollTop = stream.scrollHeight;
};

const renderDraft = (draft) => {
    const panel = document.getElementById('ai-draft-panel');
    if (!panel) {
        return;
    }

    if (!draft) {
        panel.innerHTML = '<p class="text-sm text-gray-500">No active draft.</p>';

        return;
    }

    panel.innerHTML = `
        <h4 class="font-semibold text-gray-900">Draft</h4>
        <p class="text-xs text-gray-500 uppercase">${draft.draft_type ?? ''}</p>
        <p class="mt-2 text-sm whitespace-pre-wrap">${draft.content ?? ''}</p>
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

export function initAiCoachRealtime({ coupleId, sessionId }) {
    const normalizedCoupleId = toInt(coupleId);
    const normalizedSessionId = toInt(sessionId);

    if (!normalizedCoupleId || !normalizedSessionId || !window.Echo) {
        return;
    }

    const form = document.getElementById('ai-send-form');
    if (form) {
        form.addEventListener('submit', () => {
            setThinking(true);
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

            renderDraft(event.draft);
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
