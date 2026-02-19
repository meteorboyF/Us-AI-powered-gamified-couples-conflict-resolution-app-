import './bootstrap';
import { initAiCoachRealtime } from './ai-coach-realtime';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

const aiCoachRoot = document.getElementById('ai-coach-root');
if (aiCoachRoot) {
    initAiCoachRealtime({
        coupleId: aiCoachRoot.dataset.coupleId,
        sessionId: aiCoachRoot.dataset.sessionId,
    });
}
