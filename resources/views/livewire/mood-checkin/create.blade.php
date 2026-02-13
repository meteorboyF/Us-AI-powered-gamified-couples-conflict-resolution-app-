<div class="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50">
    <div class="container mx-auto px-4 py-8">

        <!-- Header -->
        <div class="text-center mb-8">
            <h1
                class="text-4xl font-bold bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-500 bg-clip-text text-transparent mb-2">
                How Are You Feeling Today?
            </h1>
            <p class="text-gray-600">Share your mood and earn 10 XP</p>
        </div>

        <!-- Main Card -->
        <div class="max-w-2xl mx-auto">
            <div class="bg-white/80 backdrop-blur-lg rounded-3xl shadow-2xl p-8 border border-white/20">

                <!-- Mood Level Selector -->
                <div class="mb-8">
                    <label class="block text-lg font-semibold text-gray-800 mb-4 text-center">
                        How's your mood?
                    </label>
                    <div class="flex justify-between items-center gap-2">
                        @foreach([1 => '😢', 2 => '😕', 3 => '😐', 4 => '😊', 5 => '😄'] as $level => $emoji)
                            <button type="button" wire:click="$set('moodLevel', {{ $level }})"
                                class="flex-1 p-6 rounded-2xl border-2 transition-all duration-300 {{ $moodLevel === $level ? 'border-purple-500 bg-purple-50 shadow-lg scale-110' : 'border-gray-200 hover:border-purple-300 hover:scale-105' }}">
                                <div class="text-5xl mb-2">{{ $emoji }}</div>
                                <div class="text-xs font-semibold text-gray-600">{{ $level }}</div>
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Reason Tags -->
                <div class="mb-8">
                    <label class="block text-lg font-semibold text-gray-800 mb-4">
                        What's affecting your mood?
                    </label>
                    <div class="flex flex-wrap gap-3">
                        @foreach($availableReasons as $key => $label)
                            <button type="button" wire:click="toggleReason('{{ $key }}')"
                                class="px-6 py-3 rounded-full font-semibold transition-all duration-300 {{ in_array($key, $reasonTags) ? 'bg-gradient-to-r from-blue-500 to-purple-500 text-white shadow-lg scale-105' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Needs Selector -->
                <div class="mb-8">
                    <label class="block text-lg font-semibold text-gray-800 mb-4">
                        What do you need right now?
                    </label>
                    <div class="flex flex-wrap gap-3">
                        @foreach($availableNeeds as $key => $label)
                            <button type="button" wire:click="toggleNeed('{{ $key }}')"
                                class="px-6 py-3 rounded-full font-semibold transition-all duration-300 {{ in_array($key, $needs) ? 'bg-gradient-to-r from-pink-500 to-orange-500 text-white shadow-lg scale-105' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Optional Note -->
                <div class="mb-8">
                    <label class="block text-lg font-semibold text-gray-800 mb-4">
                        Anything else? (Optional)
                    </label>
                    <textarea wire:model="note" rows="4" placeholder="Share more about how you're feeling..."
                        class="w-full px-6 py-4 border-2 border-gray-200 rounded-2xl focus:border-purple-500 focus:ring-4 focus:ring-purple-200 transition-all resize-none"
                        maxlength="500"></textarea>
                    <p class="text-sm text-gray-500 mt-2 text-right">{{ strlen($note) }}/500</p>
                </div>

                <!-- Submit Button -->
                <button wire:click="submit"
                    class="w-full py-4 px-6 bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 text-white font-bold text-lg rounded-2xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300">
                    Complete Check-in ✨ (+10 XP)
                </button>

                @if (session()->has('message'))
                    <div
                        class="mt-6 p-4 bg-green-50 border-2 border-green-200 text-green-700 rounded-2xl text-center font-semibold animate-bounce-in">
                        {{ session('message') }}
                    </div>
                @endif

                @if (session()->has('error'))
                    <div class="mt-6 p-4 bg-red-50 border-2 border-red-200 text-red-700 rounded-2xl text-center">
                        {{ session('error') }}
                    </div>
                @endif
            </div>

            <!-- Info Card -->
            <div class="mt-6 p-6 bg-blue-50/80 backdrop-blur-lg rounded-2xl border border-blue-200">
                <p class="text-sm text-gray-700 text-center">
                    💡 <strong>Tip:</strong> Daily check-ins help your partner understand your emotional state and
                    support you better.
                </p>
            </div>
        </div>
    </div>
    <style>
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

        .animate-bounce-in {
            animation: bounce-in 0.6s ease-out;
        }
    </style>
</div>
