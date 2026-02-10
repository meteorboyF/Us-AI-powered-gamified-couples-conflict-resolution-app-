<div class="min-h-screen bg-gradient-to-br from-blue-50 via-purple-50 to-pink-50 flex flex-col"
    wire:poll.3s="loadMessages">
    @if($couple && $partner)
        <!-- Chat Container -->
        <div class="flex-1 flex flex-col max-w-4xl mx-auto w-full">

            <!-- Header -->
            <div class="bg-white/80 backdrop-blur-lg shadow-lg p-6 border-b border-white/20">
                <div class="flex items-center justify-between">
                    <div>
                        <h1
                            class="text-2xl font-bold bg-gradient-to-r from-blue-500 to-purple-500 bg-clip-text text-transparent">
                            Chat with {{ $partner->name }}
                        </h1>
                        <p class="text-sm text-gray-600">Your private space together 💬</p>
                    </div>
                    <a href="/dashboard" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">
                        ← Back
                    </a>
                </div>
            </div>

            <!-- Messages Area -->
            <div class="flex-1 overflow-y-auto p-6 space-y-4" id="messages-container"
                style="max-height: calc(100vh - 300px);">
                @forelse($messages as $message)
                    <div class="flex {{ $message->user_id === auth()->id() ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-xs lg:max-w-md">
                            @if($message->type === 'love_button')
                                <!-- Love Button Message -->
                                <div
                                    class="p-4 rounded-2xl {{ $message->user_id === auth()->id() ? 'bg-gradient-to-r from-pink-400 to-red-400' : 'bg-gradient-to-r from-purple-400 to-pink-400' }} text-white shadow-lg transform hover:scale-105 transition-transform">
                                    <div class="text-4xl mb-2 text-center">{{ $message->metadata['emoji'] ?? '❤️' }}</div>
                                    <p class="text-center font-semibold">{{ $message->content }}</p>
                                    <p class="text-xs text-center mt-2 opacity-90">{{ $message->created_at->diffForHumans() }}</p>
                                </div>
                            @else
                                <!-- Text Message -->
                                <div
                                    class="p-4 rounded-2xl {{ $message->user_id === auth()->id() ? 'bg-gradient-to-r from-blue-500 to-purple-500 text-white' : 'bg-white/80 backdrop-blur-lg text-gray-800' }} shadow-lg">
                                    <p class="break-words">{{ $message->content }}</p>
                                    <div class="flex items-center justify-between mt-2">
                                        <p
                                            class="text-xs {{ $message->user_id === auth()->id() ? 'text-white/80' : 'text-gray-500' }}">
                                            {{ $message->created_at->format('g:i A') }}
                                        </p>
                                        @if($message->user_id === auth()->id() && $message->read_at)
                                            <span
                                                class="text-xs {{ $message->user_id === auth()->id() ? 'text-white/80' : 'text-gray-500' }}">✓✓</span>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12">
                        <div class="text-6xl mb-4">💬</div>
                        <p class="text-gray-600 text-lg">No messages yet. Start the conversation!</p>
                    </div>
                @endforelse
            </div>

            <!-- Love Buttons Bar -->
            <div class="bg-white/80 backdrop-blur-lg p-4 border-t border-white/20">
                <div class="flex items-center justify-between gap-2 mb-2">
                    @foreach($loveButtons as $type => $button)
                        <button wire:click="sendLoveButton('{{ $type }}')" @if($remainingButtons <= 0) disabled @endif
                            class="flex-1 p-3 rounded-xl transition-all duration-300 {{ $remainingButtons > 0 ? 'bg-gradient-to-r from-pink-100 to-purple-100 hover:from-pink-200 hover:to-purple-200 hover:scale-105' : 'bg-gray-100 opacity-50 cursor-not-allowed' }}">
                            <div class="text-2xl mb-1">{{ $button['emoji'] }}</div>
                            <div class="text-xs font-semibold text-gray-700">{{ explode(' ', $button['label'])[0] }}</div>
                        </button>
                    @endforeach
                </div>
                <p class="text-xs text-center text-gray-600">
                    @if($remainingButtons > 0)
                        {{ $remainingButtons }} love button{{ $remainingButtons !== 1 ? 's' : '' }} remaining this hour
                    @else
                        Next love button available {{ $nextAvailableAt ? $nextAvailableAt->diffForHumans() : 'soon' }}
                    @endif
                </p>
            </div>

            <!-- Message Input -->
            <div class="bg-white/80 backdrop-blur-lg p-6 shadow-lg">
                <form wire:submit.prevent="sendMessage" class="flex gap-3">
                    <input type="text" wire:model="newMessage" placeholder="Type a message..."
                        class="flex-1 px-6 py-4 border-2 border-gray-200 rounded-2xl focus:border-purple-500 focus:ring-4 focus:ring-purple-200 transition-all"
                        maxlength="1000">
                    <button type="submit"
                        class="px-8 py-4 bg-gradient-to-r from-blue-500 to-purple-500 text-white font-bold rounded-2xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300">
                        Send
                    </button>
                </form>

                @if (session()->has('message'))
                    <div class="mt-4 p-3 bg-green-50 border-2 border-green-200 text-green-700 rounded-xl text-sm text-center">
                        {{ session('message') }}
                    </div>
                @endif

                @if (session()->has('error'))
                    <div class="mt-4 p-3 bg-red-50 border-2 border-red-200 text-red-700 rounded-xl text-sm text-center">
                        {{ session('error') }}
                    </div>
                @endif
            </div>
        </div>
    @else
        <!-- No Couple -->
        <div class="flex-1 flex items-center justify-center">
            <div class="text-center">
                <div class="text-8xl mb-6">💔</div>
                <h2 class="text-3xl font-bold text-gray-800 mb-4">No Couple Found</h2>
                <p class="text-gray-600 mb-8">You need to be in a couple to use chat</p>
                <a href="/couple/create-or-join"
                    class="inline-block px-8 py-4 bg-gradient-to-r from-purple-500 to-pink-500 text-white font-bold rounded-2xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all">
                    Create or Join Couple
                </a>
            </div>
        </div>
    @endif
</div>

<script>
    // Auto-scroll to bottom on new messages
    window.addEventListener('message-sent', () => {
        const container = document.getElementById('messages-container');
        if (container) {
            container.scrollTop = container.scrollHeight;
        }
    });

    // Scroll to bottom on load
    document.addEventListener('DOMContentLoaded', () => {
        const container = document.getElementById('messages-container');
        if (container) {
            container.scrollTop = container.scrollHeight;
        }
    });
</script>