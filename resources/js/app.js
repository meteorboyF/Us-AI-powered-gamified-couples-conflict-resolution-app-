import './bootstrap';
import { initChatRealtime } from './chat-realtime';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

initChatRealtime();
