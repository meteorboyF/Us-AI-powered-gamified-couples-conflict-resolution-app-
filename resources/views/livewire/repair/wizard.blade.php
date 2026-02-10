<div class="min-h-screen bg-gradient-to-br from-blue-50 via-teal-50 to-cyan-50 py-8 px-4" wire:poll.5s="loadData">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="bg-white/90 backdrop-blur-lg rounded-t-3xl shadow-lg p-6 border border-white/20">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Repair Session</h1>
                    <p class="text-sm text-gray-600">{{ $session->conflict_topic }}</p>
                </div>
                <button wire:click="abandonRepair" wire:confirm="Are you sure you want to end this repair session?"
                    class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors text-sm">
                    End Session
                </button>
            </div>

            <!-- Progress Bar -->
            <div class="mt-6">
                <div class="flex items-center justify-between mb-2">
                    @for($i = 1; $i <= 5; $i++)
                        <div class="flex-1 {{ $i < 5 ? 'mr-2' : '' }}">
                            <div
                                class="h-2 rounded-full {{ $step >= $i ? 'bg-gradient-to-r from-teal-500 to-cyan-500' : 'bg-gray-200' }}">
                            </div>
                        </div>
                    @endfor
                </div>
                <div class="flex items-center justify-between text-xs text-gray-600">
                    <span class="{{ $step === 1 ? 'font-bold text-teal-600' : '' }}">Welcome</span>
                    <span class="{{ $step === 2 ? 'font-bold text-teal-600' : '' }}">Your View</span>
                    <span class="{{ $step === 3 ? 'font-bold text-teal-600' : '' }}">Partner's View</span>
                    <span class="{{ $step === 4 ? 'font-bold text-teal-600' : '' }}">Goals</span>
                    <span class="{{ $step === 5 ? 'font-bold text-teal-600' : '' }}">Agreements</span>
                </div>
            </div>
        </div>

        <!-- Step Content -->
        <div class="bg-white/90 backdrop-blur-lg rounded-b-3xl shadow-2xl p-8 border-x border-b border-white/20">

            @if($step === 1)
                <!-- Step 1: Welcome -->
                <div class="text-center">
                    <div class="text-6xl mb-6">🤝</div>
                    <h2 class="text-3xl font-bold text-gray-800 mb-4">Let's Work Through This Together</h2>
                    <p class="text-gray-600 mb-8 max-w-2xl mx-auto">
                        This repair process will help you both understand each other better and find common ground.
                        Remember: we're on the same team.
                    </p>

                    <div class="bg-teal-50 border-2 border-teal-200 rounded-2xl p-6 mb-8 text-left max-w-xl mx-auto">
                        <h3 class="font-bold text-teal-900 mb-3">Ground Rules:</h3>
                        <ul class="space-y-2 text-teal-800">
                            <li class="flex items-start gap-2">
                                <span>✓</span>
                                <span>Speak from your own perspective ("I feel...")</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span>✓</span>
                                <span>Listen to understand, not to respond</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span>✓</span>
                                <span>No blame or criticism</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span>✓</span>
                                <span>Focus on solutions, not problems</span>
                            </li>
                        </ul>
                    </div>

                    <button wire:click="nextStep"
                        class="px-12 py-4 bg-gradient-to-r from-teal-500 to-cyan-500 text-white font-bold rounded-2xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all">
                        Let's Begin
                    </button>
                </div>

            @elseif($step === 2)
                <!-- Step 2: Your Perspective -->
                <div>
                    <div class="text-center mb-8">
                        <div class="text-5xl mb-4">💭</div>
                        <h2 class="text-2xl font-bold text-gray-800 mb-2">Share Your Perspective</h2>
                        <p class="text-gray-600">Express how you feel and what you need</p>
                    </div>

                    <form wire:submit.prevent="savePerspective">
                        <div class="mb-6">
                            <label class="block text-gray-700 font-semibold mb-3">
                                How do you see this situation? What do you need?
                            </label>
                            <textarea wire:model="myPerspective" rows="8" placeholder="I feel... I need... I hope..."
                                class="w-full px-6 py-4 border-2 border-gray-200 rounded-2xl focus:border-teal-500 focus:ring-4 focus:ring-teal-200 transition-all resize-none"
                                maxlength="500"></textarea>
                            <div class="flex justify-between items-center mt-2">
                                <span class="text-sm text-gray-500">{{ strlen($myPerspective) }}/500 characters</span>
                                @error('myPerspective')
                                    <p class="text-red-600 text-sm">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <button type="button" wire:click="prevStep"
                                class="px-8 py-3 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors font-semibold">
                                ← Back
                            </button>
                            <button type="submit"
                                class="flex-1 py-3 bg-gradient-to-r from-teal-500 to-cyan-500 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all">
                                Save & Continue
                            </button>
                        </div>
                    </form>
                </div>

            @elseif($step === 3)
                <!-- Step 3: Partner's Perspective -->
                <div>
                    <div class="text-center mb-8">
                        <div class="text-5xl mb-4">👂</div>
                        <h2 class="text-2xl font-bold text-gray-800 mb-2">Your Partner's Perspective</h2>
                        <p class="text-gray-600">Take a moment to understand their view</p>
                    </div>

                    @if($partnerPerspective)
                        <div class="bg-blue-50 border-2 border-blue-200 rounded-2xl p-6 mb-8">
                            <p class="text-blue-900 whitespace-pre-wrap">{{ $partnerPerspective }}</p>
                        </div>

                        <div class="bg-teal-50 border-2 border-teal-200 rounded-2xl p-6 mb-8">
                            <p class="text-teal-900 text-sm">
                                💙 <strong>Remember:</strong> Your partner's feelings are valid, even if you see things
                                differently.
                                Understanding doesn't mean agreeing—it means caring.
                            </p>
                        </div>

                        <div class="flex gap-4">
                            <button wire:click="prevStep"
                                class="px-8 py-3 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors font-semibold">
                                ← Back
                            </button>
                            <button wire:click="nextStep"
                                class="flex-1 py-3 bg-gradient-to-r from-teal-500 to-cyan-500 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all">
                                Continue
                            </button>
                        </div>
                    @else
                        <div class="text-center py-12">
                            <div class="text-6xl mb-4">⏳</div>
                            <p class="text-gray-600">Waiting for your partner to share their perspective...</p>
                            <p class="text-sm text-gray-500 mt-2">This page will update automatically</p>
                        </div>
                    @endif
                </div>

            @elseif($step === 4)
                <!-- Step 4: Shared Goals -->
                <div>
                    <div class="text-center mb-8">
                        <div class="text-5xl mb-4">🎯</div>
                        <h2 class="text-2xl font-bold text-gray-800 mb-2">Find Common Ground</h2>
                        <p class="text-gray-600">Select 3-5 goals you both want to work on</p>
                    </div>

                    <form wire:submit.prevent="saveGoals">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                            @foreach($sharedGoals as $key => $label)
                                <label class="cursor-pointer">
                                    <input type="checkbox" wire:model="selectedGoals" value="{{ $key }}" class="hidden peer">
                                    <div
                                        class="p-4 border-2 border-gray-200 rounded-xl peer-checked:border-teal-500 peer-checked:bg-teal-50 transition-all hover:border-teal-300">
                                        <span class="font-semibold text-gray-800">{{ $label }}</span>
                                    </div>
                                </label>
                            @endforeach
                        </div>

                        <p class="text-sm text-gray-600 mb-6 text-center">
                            Selected: {{ count($selectedGoals) }}/5 (minimum 3 required)
                        </p>

                        @error('selectedGoals')
                            <p class="text-red-600 text-sm text-center mb-4">{{ $message }}</p>
                        @enderror

                        <div class="flex gap-4">
                            <button type="button" wire:click="prevStep"
                                class="px-8 py-3 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors font-semibold">
                                ← Back
                            </button>
                            <button type="submit"
                                class="flex-1 py-3 bg-gradient-to-r from-teal-500 to-cyan-500 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all">
                                Save & Continue
                            </button>
                        </div>
                    </form>
                </div>

            @elseif($step === 5)
                <!-- Step 5: Agreements -->
                <div>
                    <div class="text-center mb-8">
                        <div class="text-5xl mb-4">📝</div>
                        <h2 class="text-2xl font-bold text-gray-800 mb-2">Create Agreements</h2>
                        <p class="text-gray-600">Make commitments to each other</p>
                    </div>

                    <!-- Add New Agreement -->
                    <form wire:submit.prevent="addAgreement" class="mb-8">
                        <div class="flex gap-3">
                            <input type="text" wire:model="newAgreement" placeholder="I commit to..."
                                class="flex-1 px-6 py-3 border-2 border-gray-200 rounded-xl focus:border-teal-500 focus:ring-4 focus:ring-teal-200 transition-all"
                                maxlength="300">
                            <button type="submit"
                                class="px-6 py-3 bg-gradient-to-r from-teal-500 to-cyan-500 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all">
                                Add
                            </button>
                        </div>
                        @error('newAgreement')
                            <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </form>

                    <!-- Agreements List -->
                    <div class="space-y-4 mb-8">
                        @forelse($agreements as $agreement)
                            <div
                                class="p-4 border-2 {{ $agreement->created_by === auth()->id() ? 'border-teal-200 bg-teal-50' : 'border-blue-200 bg-blue-50' }} rounded-xl">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex-1">
                                        <p
                                            class="font-semibold {{ $agreement->created_by === auth()->id() ? 'text-teal-900' : 'text-blue-900' }}">
                                            {{ $agreement->created_by === auth()->id() ? 'You' : 'Partner' }}:
                                        </p>
                                        <p class="text-gray-800 mt-1">{{ $agreement->agreement_text }}</p>
                                    </div>
                                    @if($agreement->created_by !== auth()->id())
                                        @if($agreement->isAcknowledged())
                                            <span class="text-green-600 font-semibold">✓ Acknowledged</span>
                                        @else
                                            <button wire:click="acknowledgeAgreement({{ $agreement->id }})"
                                                class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg transition-colors text-sm font-semibold">
                                                Acknowledge
                                            </button>
                                        @endif
                                    @else
                                        @if($agreement->isAcknowledged())
                                            <span class="text-green-600 font-semibold">✓ Acknowledged</span>
                                        @else
                                            <span class="text-gray-500 text-sm">Waiting...</span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-center text-gray-500 py-8">No agreements yet. Add your first commitment above!</p>
                        @endforelse
                    </div>

                    @if (session()->has('message'))
                        <div
                            class="mb-6 p-3 bg-green-50 border-2 border-green-200 text-green-700 rounded-xl text-center text-sm">
                            {{ session('message') }}
                        </div>
                    @endif

                    @if (session()->has('error'))
                        <div class="mb-6 p-3 bg-red-50 border-2 border-red-200 text-red-700 rounded-xl text-center text-sm">
                            {{ session('error') }}
                        </div>
                    @endif

                    <!-- Complete Button -->
                    <div class="flex gap-4">
                        <button wire:click="prevStep"
                            class="px-8 py-3 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors font-semibold">
                            ← Back
                        </button>
                        <button wire:click="completeRepair"
                            class="flex-1 py-3 bg-gradient-to-r from-green-500 to-teal-500 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all">
                            ✨ Complete Repair (+50 XP)
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>