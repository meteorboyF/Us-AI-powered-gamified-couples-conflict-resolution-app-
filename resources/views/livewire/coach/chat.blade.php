<div
    class="min-h-screen {{ $mode === 'vent' ? 'bg-gradient-to-br from-orange-50 via-amber-50 to-orange-100' : 'bg-gradient-to-br from-blue-50 via-teal-50 to-emerald-50' }} py-6 transition-colors duration-500">
    <div class="max-w-4xl mx-auto px-4 h-full flex flex-col">

        <!-- Header / Mode Switcher -->
        <div
            class="flex flex-col md:flex-row justify-between items-center mb-6 bg-white/80 backdrop-blur-md p-4 rounded-2xl shadow-sm border border-white/50">
            <div class="flex items-center gap-3">
                <div class="text-3xl">{{ $mode === 'vent' ? '🧡' : '🌉' }}</div>
                <div>
                    <h1
                        class="text-2xl font-bold bg-clip-text text-transparent {{ $mode === 'vent' ? 'bg-gradient-to-r from-orange-500 to-amber-600' : 'bg-gradient-to-r from-teal-500 to-blue-600' }}">
                        {{ $mode === 'vent' ? 'Venting Space' : 'Bridge Builder' }}
                    </h1>
                    <p class="text-gray-500 text-sm">
                        {{ $mode === 'vent' ? 'Safe, private validation.' : 'Reframing for connection.' }}
                    </p>
                </div>
            </div>

            <div class="flex bg-gray-100 p-1 rounded-xl mt-4 md:mt-0">
                <button wire:click="switchMode('vent')"
                    class="px-4 py-2 rounded-lg text-sm font-semibold transition-all {{ $mode === 'vent' ? 'bg-white shadow text-orange-600' : 'text-gray-500 hover:text-gray-700' }}">
                    🧡 Venting
                </button>
                <button wire:click="switchMode('bridge')"
                    class="px-4 py-2 rounded-lg text-sm font-semibold transition-all {{ $mode === 'bridge' ? 'bg-white shadow text-teal-600' : 'text-gray-500 hover:text-gray-700' }}">
                    🌉 Bridge
                </button>
            </div>
        </div>

        <!-- Chat Area -->
        <div class="flex-1 overflow-y-auto mb-6 space-y-4 p-4 min-h-[50vh] max-h-[60vh] custom-scrollbar"
            id="chat-container">
            @foreach($messages as $msg)
                    <div class="flex {{ $msg['role'] === 'user' ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[80%] md:max-w-[70%] p-4 rounded-2xl shadow-sm 
                                {{ $msg['role'] === 'user'
                ? ($mode === 'vent' ? 'bg-orange-500 text-white rounded-tr-none' : 'bg-teal-600 text-white rounded-tr-none')
                : 'bg-white text-gray-800 rounded-tl-none border border-gray-100' 
                                }}">
                            <p class="whitespace-pre-wrap leading-relaxed">{{ $msg['content'] }}</p>
                        </div>
                    </div>
            @endforeach

            <!-- Typing Indicator -->
            @if($isTyping)
                <div class="flex justify-start animate-pulse">
                    <div class="bg-gray-200 p-4 rounded-2xl rounded-tl-none flex gap-2 items-center">
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></div>
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                    </div>
                </div>
                <!-- Trigger response generation -->
                <div wire:init="generateResponse" class="hidden"></div>
            @endif

            <div id="scroll-anchor"></div>
        </div>

        <!-- Input Area -->
        <div class="bg-white/90 backdrop-blur-xl p-4 rounded-2xl shadow-lg border border-white/50 sticky bottom-4">
            <form wire:submit.prevent="sendMessage">
                <div class="relative">
                    <input type="text" wire:model="newMessage"
                        placeholder="{{ $mode === 'vent' ? 'Tell me what frustrated you...' : 'What do you want to say to your partner?' }}"
                        class="w-full pl-6 pr-14 py-4 rounded-xl border-gray-200 bg-gray-50 focus:bg-white focus:ring-4 transition-all
                            {{ $mode === 'vent' ? 'focus:border-orange-300 focus:ring-orange-100' : 'focus:border-teal-300 focus:ring-teal-100' }}"
                        autofocus>
                    <button type="submit"
                        class="absolute right-2 top-2 p-2 rounded-lg text-white transition-all transform active:scale-95 disabled:opacity-50
                        {{ $mode === 'vent' ? 'bg-orange-500 hover:bg-orange-600 shadow-orange-200' : 'bg-teal-500 hover:bg-teal-600 shadow-teal-200' }} shadow-lg"
                        wire:loading.attr="disabled" {{ empty(trim($newMessage)) ? 'disabled' : '' }}>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6">
                            <path
                                d="M3.478 2.405a.75.75 0 00-.926.94l2.432 7.905H13.5a.75.75 0 010 1.5H4.984l-2.432 7.905a.75.75 0 00.926.94 60.519 60.519 0 0018.445-8.986.75.75 0 000-1.218A60.517 60.517 0 003.478 2.405z" />
                        </svg>
                    </button>
                </div>
            </form>
            <div class="mt-2 text-center">
                <p class="text-xs text-gray-400">
                    AI can make mistakes. Please use your best judgment.
                    <a href="#" class="underline hover:text-gray-600">Safety Guidelines</a>
                </p>
            </div>
        </div>
    </div>
</div>

<script>
    // Auto-scroll to bottom
    document.addEventListener('livewire:initialized', () => {
        const scrollToBottom = () => {
            const container = document.getElementById('chat-container');
            if (container) container.scrollTop = container.scrollHeight;
        };

        Livewire.hook('morph.updated', ({ component, el }) => {
            scrollToBottom();
        });

        scrollToBottom();
    });
</script>