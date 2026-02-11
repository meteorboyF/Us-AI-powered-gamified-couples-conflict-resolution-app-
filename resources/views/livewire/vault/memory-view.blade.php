<div class="min-h-screen bg-gradient-to-br from-purple-50 via-pink-50 to-rose-50 py-12 px-4">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white/90 backdrop-blur-lg rounded-3xl shadow-2xl overflow-hidden border border-white/20">
            <!-- Header -->
            <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">{{ $memory->title ?? 'Memory' }}</h2>
                    <p class="text-sm text-gray-600">{{ $memory->creator->name }} •
                        {{ $memory->created_at->format('M d, Y') }}</p>
                </div>
                <a href="/vault" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">
                    ← Back
                </a>
            </div>

            <!-- Media Display -->
            <div class="p-8">
                @if($memory->isPhoto())
                    <img src="{{ $memory->getFileUrl() }}" alt="{{ $memory->title }}" class="w-full rounded-2xl shadow-lg">
                @elseif($memory->isVideo())
                    <video controls class="w-full rounded-2xl shadow-lg">
                        <source src="{{ $memory->getFileUrl() }}" type="{{ $memory->mime_type }}">
                    </video>
                @elseif($memory->isVoiceNote())
                    <div class="bg-gradient-to-br from-purple-100 to-pink-100 rounded-2xl p-12 text-center">
                        <div class="text-8xl mb-6">🎤</div>
                        <audio controls class="w-full">
                            <source src="{{ $memory->getFileUrl() }}" type="{{ $memory->mime_type }}">
                        </audio>
                    </div>
                @else
                    <div class="bg-gradient-to-br from-purple-100 to-pink-100 rounded-2xl p-8">
                        <div class="text-6xl mb-4">📝</div>
                        <p class="text-gray-800 text-lg whitespace-pre-wrap">{{ $memory->description }}</p>
                    </div>
                @endif

                @if($memory->description && !$memory->isText())
                    <div class="mt-6 p-6 bg-gray-50 rounded-2xl">
                        <p class="text-gray-800">{{ $memory->description }}</p>
                    </div>
                @endif
            </div>

            <!-- Reactions -->
            <div class="px-8 pb-8">
                <div class="bg-purple-50 rounded-2xl p-6">
                    <h3 class="font-semibold text-gray-800 mb-4">Reactions</h3>
                    <div class="flex gap-3 mb-4">
                        @foreach($reactions as $key => $emoji)
                            <button wire:click="addReaction('{{ $key }}')"
                                class="text-3xl hover:scale-125 transition-transform {{ $myReaction && $myReaction->reaction === $key ? 'scale-125' : '' }}">
                                {{ $emoji }}
                            </button>
                        @endforeach
                        @if($myReaction)
                            <button wire:click="removeReaction"
                                class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg text-sm">
                                Remove
                            </button>
                        @endif
                    </div>
                    @if($partnerReaction)
                        <p class="text-gray-600 text-sm">
                            Your partner reacted: {{ $partnerReaction->getEmoji() }}
                        </p>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            @if($memory->created_by === auth()->id())
                <div class="px-8 pb-8 flex gap-3">
                    @if(!$memory->isLocked())
                        <button wire:click="lockMemory"
                            class="flex-1 py-3 bg-gradient-to-r from-yellow-500 to-amber-500 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all">
                            🔒 Lock This Memory (+10 XP)
                        </button>
                        <button wire:click="deleteMemory" wire:confirm="Are you sure you want to delete this memory?"
                            class="px-6 py-3 bg-red-500 hover:bg-red-600 text-white font-bold rounded-xl transition-colors">
                            Delete
                        </button>
                    @else
                        <div
                            class="flex-1 py-3 bg-gradient-to-r from-yellow-500 to-amber-500 text-white font-bold rounded-xl text-center">
                            🔒 Locked Memory
                        </div>
                    @endif
                </div>
            @endif

            @if (session()->has('message'))
                <div class="mx-8 mb-8 p-4 bg-green-50 border-2 border-green-200 text-green-700 rounded-xl text-center">
                    {{ session('message') }}
                </div>
            @endif
        </div>
    </div>
</div>