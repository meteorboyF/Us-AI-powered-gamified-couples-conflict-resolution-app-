<div class="min-h-screen bg-gradient-to-br from-blue-50 via-teal-50 to-cyan-50 py-12 px-4">
    <div class="max-w-2xl mx-auto">
        @if($couple)
            @if($activeSession)
                <!-- Active Session Exists -->
                <div class="bg-white/90 backdrop-blur-lg rounded-3xl shadow-2xl p-8 border border-white/20">
                    <div class="text-center mb-8">
                        <div class="text-6xl mb-4">🛠️</div>
                        <h1 class="text-3xl font-bold text-gray-800 mb-2">Active Repair Session</h1>
                        <p class="text-gray-600">There's already a repair session in progress</p>
                    </div>

                    <div class="bg-teal-50 border-2 border-teal-200 rounded-2xl p-6 mb-6">
                        <p class="font-semibold text-teal-900 mb-2">Topic:</p>
                        <p class="text-teal-700">{{ $activeSession->conflict_topic }}</p>
                    </div>

                    <button wire:click="joinActiveSession"
                        class="w-full py-4 bg-gradient-to-r from-teal-500 to-cyan-500 text-white font-bold rounded-2xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300">
                        Continue Repair Session
                    </button>

                    <a href="/dashboard" class="block text-center mt-4 text-gray-600 hover:text-gray-800">
                        ← Back to Dashboard
                    </a>
                </div>
            @else
                <!-- Start New Repair -->
                <div class="bg-white/90 backdrop-blur-lg rounded-3xl shadow-2xl p-8 border border-white/20">
                    <div class="text-center mb-8">
                        <div class="text-6xl mb-4">💙</div>
                        <h1 class="text-3xl font-bold text-gray-800 mb-2">Start a Repair Session</h1>
                        <p class="text-gray-600">Let's work through this together</p>
                    </div>

                    <!-- Explanation -->
                    <div class="bg-blue-50 border-2 border-blue-200 rounded-2xl p-6 mb-8">
                        <h3 class="font-bold text-blue-900 mb-3">How Repair Works:</h3>
                        <ul class="space-y-2 text-blue-800 text-sm">
                            <li class="flex items-start gap-2">
                                <span class="text-lg">1️⃣</span>
                                <span>Both partners share their perspective</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-lg">2️⃣</span>
                                <span>Find shared goals and values</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-lg">3️⃣</span>
                                <span>Create mutual agreements</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-lg">✨</span>
                                <span>Earn +50 XP for completing together</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Form -->
                    <form wire:submit.prevent="startRepair">
                        <div class="mb-6">
                            <label class="block text-gray-700 font-semibold mb-2">
                                What would you like to work on?
                            </label>
                            <input type="text" wire:model="conflictTopic"
                                placeholder="e.g., Communication about household chores"
                                class="w-full px-6 py-4 border-2 border-gray-200 rounded-2xl focus:border-teal-500 focus:ring-4 focus:ring-teal-200 transition-all"
                                maxlength="200">
                            @error('conflictTopic')
                                <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit"
                            class="w-full py-4 bg-gradient-to-r from-teal-500 to-cyan-500 text-white font-bold rounded-2xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300">
                            Start Repair Session
                        </button>
                    </form>

                    @if (session()->has('message'))
                        <div class="mt-6 p-4 bg-green-50 border-2 border-green-200 text-green-700 rounded-xl text-center">
                            {{ session('message') }}
                        </div>
                    @endif

                    @if (session()->has('error'))
                        <div class="mt-6 p-4 bg-red-50 border-2 border-red-200 text-red-700 rounded-xl text-center">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="mt-8 flex gap-4">
                        <a href="/dashboard"
                            class="flex-1 text-center py-3 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors text-gray-700 font-semibold">
                            ← Back
                        </a>
                        <a href="/repair/history"
                            class="flex-1 text-center py-3 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors text-gray-700 font-semibold">
                            View History
                        </a>
                    </div>
                </div>
            @endif
        @else
            <!-- No Couple -->
            <div class="bg-white/90 backdrop-blur-lg rounded-3xl shadow-2xl p-8 border border-white/20 text-center">
                <div class="text-8xl mb-6">💔</div>
                <h2 class="text-3xl font-bold text-gray-800 mb-4">No Couple Found</h2>
                <p class="text-gray-600 mb-8">You need to be in a couple to use repair sessions</p>
                <a href="/couple/create-or-join"
                    class="inline-block px-8 py-4 bg-gradient-to-r from-teal-500 to-cyan-500 text-white font-bold rounded-2xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all">
                    Create or Join Couple
                </a>
            </div>
        @endif
    </div>
</div>