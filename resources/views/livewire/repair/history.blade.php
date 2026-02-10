<div class="min-h-screen bg-gradient-to-br from-blue-50 via-teal-50 to-cyan-50 py-12 px-4">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white/90 backdrop-blur-lg rounded-3xl shadow-2xl p-8 border border-white/20">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">Repair History</h1>
                    <p class="text-gray-600">Your journey of growth together</p>
                </div>
                <a href="/dashboard" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">
                    ← Back
                </a>
            </div>

            @if($couple)
                @forelse($sessions as $session)
                    <div class="mb-4 bg-gradient-to-r from-teal-50 to-cyan-50 border-2 border-teal-200 rounded-2xl p-6 hover:shadow-lg transition-shadow cursor-pointer"
                        wire:click="viewSession({{ $session->id }})">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <span class="text-2xl">✨</span>
                                    <h3 class="font-bold text-gray-800">{{ $session->conflict_topic }}</h3>
                                </div>
                                <div class="flex items-center gap-4 text-sm text-gray-600">
                                    <span>📅 {{ $session->completed_at->format('M d, Y') }}</span>
                                    <span>📝 {{ $session->agreements->count() }} agreements</span>
                                    <span>⭐ +50 XP</span>
                                </div>
                            </div>
                            <button class="text-teal-600 hover:text-teal-700 font-semibold">
                                View Details →
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-16">
                        <div class="text-8xl mb-6">🌱</div>
                        <h2 class="text-2xl font-bold text-gray-800 mb-4">No Repairs Yet</h2>
                        <p class="text-gray-600 mb-8">Start your first repair session to begin growing together</p>
                        <a href="/repair/initiate"
                            class="inline-block px-8 py-4 bg-gradient-to-r from-teal-500 to-cyan-500 text-white font-bold rounded-2xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all">
                            Start First Repair
                        </a>
                    </div>
                @endforelse
            @else
                <div class="text-center py-16">
                    <div class="text-8xl mb-6">💔</div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-4">No Couple Found</h2>
                    <p class="text-gray-600 mb-8">You need to be in a couple to view repair history</p>
                    <a href="/couple/create-or-join"
                        class="inline-block px-8 py-4 bg-gradient-to-r from-teal-500 to-cyan-500 text-white font-bold rounded-2xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all">
                        Create or Join Couple
                    </a>
                </div>
            @endif
        </div>

        <!-- Session Details Modal -->
        @if($selectedSession)
            <div class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50" wire:click="closeDetails">
                <div class="bg-white rounded-3xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto" wire:click.stop>
                    <div class="p-8">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-2xl font-bold text-gray-800">Session Details</h2>
                            <button wire:click="closeDetails" class="text-gray-500 hover:text-gray-700 text-2xl">×</button>
                        </div>

                        <div class="space-y-6">
                            <!-- Topic -->
                            <div>
                                <h3 class="font-semibold text-gray-700 mb-2">Topic:</h3>
                                <p class="text-gray-800">{{ $selectedSession->conflict_topic }}</p>
                            </div>

                            <!-- Date -->
                            <div>
                                <h3 class="font-semibold text-gray-700 mb-2">Completed:</h3>
                                <p class="text-gray-800">{{ $selectedSession->completed_at->format('F d, Y \a\t g:i A') }}
                                </p>
                            </div>

                            <!-- Shared Goals -->
                            @if($selectedSession->shared_goals)
                                <div>
                                    <h3 class="font-semibold text-gray-700 mb-2">Shared Goals:</h3>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($selectedSession->shared_goals as $goal)
                                            <span class="px-3 py-1 bg-teal-100 text-teal-800 rounded-full text-sm">
                                                {{ $sharedGoals[$goal] ?? $goal }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Agreements -->
                            <div>
                                <h3 class="font-semibold text-gray-700 mb-3">Agreements
                                    ({{ $selectedSession->agreements->count() }}):</h3>
                                <div class="space-y-3">
                                    @foreach($selectedSession->agreements as $agreement)
                                        <div class="p-4 bg-teal-50 border-2 border-teal-200 rounded-xl">
                                            <p class="text-gray-800">{{ $agreement->agreement_text }}</p>
                                            <p class="text-sm text-teal-600 mt-2">
                                                ✓ Acknowledged
                                            </p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <button wire:click="closeDetails"
                            class="w-full mt-8 py-3 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors font-semibold">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>