<div class="min-h-screen bg-gradient-to-br from-pink-50 via-purple-50 to-blue-50">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1
                class="text-5xl font-bold bg-gradient-to-r from-pink-500 via-purple-500 to-blue-500 bg-clip-text text-transparent mb-4">
                Welcome to Us 💕
            </h1>
            <p class="text-gray-600 text-lg">Create or join your couple to start your journey together</p>
        </div>

        <!-- Main Card -->
        <div class="max-w-4xl mx-auto">
            <div class="bg-white/80 backdrop-blur-lg rounded-3xl shadow-2xl p-8 border border-white/20">

                <!-- Tabs -->
                <div class="flex gap-4 mb-8">
                    <button wire:click="$set('tab', 'create')"
                        class="flex-1 py-4 px-6 rounded-2xl font-semibold transition-all duration-300 {{ $tab === 'create' ? 'bg-gradient-to-r from-pink-500 to-purple-500 text-white shadow-lg scale-105' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        <span class="text-2xl mr-2">✨</span>
                        Create Couple
                    </button>
                    <button wire:click="$set('tab', 'join')"
                        class="flex-1 py-4 px-6 rounded-2xl font-semibold transition-all duration-300 {{ $tab === 'join' ? 'bg-gradient-to-r from-blue-500 to-purple-500 text-white shadow-lg scale-105' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        <span class="text-2xl mr-2">🔗</span>
                        Join Couple
                    </button>
                </div>

                @if($tab === 'create')
                    <!-- Create Couple Form -->
                    <div class="space-y-6 animate-fade-in">
                        <div class="text-center p-6 bg-gradient-to-r from-pink-100 to-purple-100 rounded-2xl">
                            <p class="text-lg text-gray-700 mb-4">Start your shared world together!</p>
                            <p class="text-sm text-gray-600">Choose a theme for your couple's world</p>
                        </div>

                        <!-- Theme Selection -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">Choose Your World Theme</label>
                            <div class="grid grid-cols-2 gap-4">
                                @foreach(['garden' => '🌸', 'house' => '🏡', 'kitchen' => '🍳', 'farm' => '🌾'] as $theme => $emoji)
                                    <button type="button" wire:click="$set('selectedTheme', '{{ $theme }}')"
                                        class="p-6 rounded-2xl border-2 transition-all duration-300 {{ $selectedTheme === $theme ? 'border-purple-500 bg-purple-50 shadow-lg scale-105' : 'border-gray-200 hover:border-purple-300 hover:shadow-md' }}">
                                        <div class="text-5xl mb-2">{{ $emoji }}</div>
                                        <div class="font-semibold capitalize text-gray-700">{{ $theme }}</div>
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <button wire:click="createCouple"
                            class="w-full py-4 px-6 bg-gradient-to-r from-pink-500 via-purple-500 to-blue-500 text-white font-bold rounded-2xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300">
                            Create Our World ✨
                        </button>

                        @if($inviteCode)
                            <div
                                class="p-6 bg-gradient-to-r from-green-50 to-emerald-50 rounded-2xl border-2 border-green-200 animate-bounce-in">
                                <p class="text-sm font-semibold text-gray-700 mb-2">Share this code with your partner:</p>
                                <div class="flex items-center gap-3">
                                    <code
                                        class="flex-1 text-3xl font-bold text-center py-4 bg-white rounded-xl text-purple-600 tracking-wider">
                                                {{ $inviteCode }}
                                            </code>
                                    <button onclick="navigator.clipboard.writeText('{{ $inviteCode }}')"
                                        class="px-6 py-4 bg-purple-500 text-white rounded-xl hover:bg-purple-600 transition-colors">
                                        📋 Copy
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                @else
                    <!-- Join Couple Form -->
                    <div class="space-y-6 animate-fade-in">
                        <div class="text-center p-6 bg-gradient-to-r from-blue-100 to-purple-100 rounded-2xl">
                            <p class="text-lg text-gray-700 mb-4">Join your partner's world!</p>
                            <p class="text-sm text-gray-600">Enter the invite code they shared with you</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">Invite Code</label>
                            <input type="text" wire:model="joinCode" placeholder="Enter 8-character code"
                                class="w-full px-6 py-4 text-2xl font-bold text-center tracking-wider border-2 border-gray-200 rounded-2xl focus:border-purple-500 focus:ring-4 focus:ring-purple-200 transition-all uppercase"
                                maxlength="8">
                        </div>

                        <button wire:click="joinCouple"
                            class="w-full py-4 px-6 bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 text-white font-bold rounded-2xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300">
                            Join Our World 🔗
                        </button>
                    </div>
                @endif

                @if (session()->has('message'))
                    <div
                        class="mt-6 p-4 bg-green-50 border-2 border-green-200 text-green-700 rounded-2xl animate-bounce-in">
                        {{ session('message') }}
                    </div>
                @endif

                @if (session()->has('error'))
                    <div class="mt-6 p-4 bg-red-50 border-2 border-red-200 text-red-700 rounded-2xl animate-shake">
                        {{ session('error') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes fade-in {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes bounce-in {
        0% {
            opacity: 0;
            transform: scale(0.9);
        }

        50% {
            transform: scale(1.05);
        }

        100% {
            opacity: 1;
            transform: scale(1);
        }
    }

    @keyframes shake {

        0%,
        100% {
            transform: translateX(0);
        }

        25% {
            transform: translateX(-10px);
        }

        75% {
            transform: translateX(10px);
        }
    }

    .animate-fade-in {
        animation: fade-in 0.5s ease-out;
    }

    .animate-bounce-in {
        animation: bounce-in 0.6s ease-out;
    }

    .animate-shake {
        animation: shake 0.5s ease-out;
    }
</style>