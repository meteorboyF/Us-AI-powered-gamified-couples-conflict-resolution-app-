<div class="min-h-screen bg-gradient-to-br from-purple-50 via-pink-50 to-rose-50 py-8 px-4">
    <div class="max-w-7xl mx-auto">
        @if($couple)
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-4xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent mb-2">
                        Memory Vault
                    </h1>
                    <p class="text-gray-600">Your love story, captured</p>
                </div>
                <div class="flex gap-3">
                    <a href="/dashboard" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">
                        Back
                    </a>
                    <a href="/vault/upload"
                        class="px-6 py-3 bg-gradient-to-r from-purple-500 to-pink-500 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all">
                        + Add Memory
                    </a>
                </div>
            </div>

            @if($storageStats)
                <div class="bg-white/80 backdrop-blur-lg rounded-2xl p-6 shadow-lg border border-white/20 mb-8">
                    <div class="grid grid-cols-2 md:grid-cols-7 gap-4">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-purple-600">{{ $storageStats['total_count'] }}</div>
                            <div class="text-sm text-gray-600">Total</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-pink-600">{{ $storageStats['photos'] }}</div>
                            <div class="text-sm text-gray-600">Photos</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-purple-600">{{ $storageStats['videos'] }}</div>
                            <div class="text-sm text-gray-600">Videos</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-pink-600">{{ $storageStats['voice_notes'] }}</div>
                            <div class="text-sm text-gray-600">Voice</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-purple-600">{{ $storageStats['text'] }}</div>
                            <div class="text-sm text-gray-600">Text</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-yellow-600">{{ $storageStats['locked'] }}</div>
                            <div class="text-sm text-gray-600">Dual</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-rose-600">{{ $storageStats['comfort'] ?? 0 }}</div>
                            <div class="text-sm text-gray-600">Comfort</div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="flex flex-wrap gap-2 mb-8">
                <button wire:click="filterByType('all')"
                    class="px-4 py-2 rounded-xl font-semibold transition-all {{ $filterType === 'all' && !$showLocked ? 'bg-gradient-to-r from-purple-500 to-pink-500 text-white shadow-lg' : 'bg-white/80 text-gray-700 hover:bg-white' }}">
                    All
                </button>
                <button wire:click="filterByType('photo')"
                    class="px-4 py-2 rounded-xl font-semibold transition-all {{ $filterType === 'photo' ? 'bg-gradient-to-r from-purple-500 to-pink-500 text-white shadow-lg' : 'bg-white/80 text-gray-700 hover:bg-white' }}">
                    Photos
                </button>
                <button wire:click="filterByType('video')"
                    class="px-4 py-2 rounded-xl font-semibold transition-all {{ $filterType === 'video' ? 'bg-gradient-to-r from-purple-500 to-pink-500 text-white shadow-lg' : 'bg-white/80 text-gray-700 hover:bg-white' }}">
                    Videos
                </button>
                <button wire:click="filterByType('voice_note')"
                    class="px-4 py-2 rounded-xl font-semibold transition-all {{ $filterType === 'voice_note' ? 'bg-gradient-to-r from-purple-500 to-pink-500 text-white shadow-lg' : 'bg-white/80 text-gray-700 hover:bg-white' }}">
                    Voice Notes
                </button>
                <button wire:click="filterByType('text')"
                    class="px-4 py-2 rounded-xl font-semibold transition-all {{ $filterType === 'text' ? 'bg-gradient-to-r from-purple-500 to-pink-500 text-white shadow-lg' : 'bg-white/80 text-gray-700 hover:bg-white' }}">
                    Text
                </button>
                <button wire:click="toggleLockedView"
                    class="px-4 py-2 rounded-xl font-semibold transition-all {{ $showLocked ? 'bg-gradient-to-r from-yellow-500 to-amber-500 text-white shadow-lg' : 'bg-white/80 text-gray-700 hover:bg-white' }}">
                    Dual
                </button>
                <button wire:click="toggleComfortView"
                    class="px-4 py-2 rounded-xl font-semibold transition-all {{ $showComfort ? 'bg-gradient-to-r from-rose-500 to-orange-500 text-white shadow-lg' : 'bg-white/80 text-gray-700 hover:bg-white' }}">
                    Comfort
                </button>
            </div>

            @if (session()->has('message'))
                <div class="mb-6 p-4 bg-green-50 border-2 border-green-200 text-green-700 rounded-xl text-center">
                    {{ session('message') }}
                </div>
            @endif

            @if (session()->has('error'))
                <div class="mb-6 p-4 bg-red-50 border-2 border-red-200 text-red-700 rounded-xl text-center">
                    {{ session('error') }}
                </div>
            @endif

            @if($memories && $memories->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($memories as $memory)
                        @php
                            $isDualLocked = $memory->isDual() && !$memory->hasActiveDualUnlock();
                        @endphp
                        <div wire:key="memory-card-{{ $memory->id }}"
                            class="group relative bg-white/90 backdrop-blur-lg rounded-2xl shadow-lg hover:shadow-2xl transform hover:scale-105 transition-all duration-300 overflow-hidden border border-white/20">
                            <a href="/vault/memory/{{ $memory->id }}" class="block">
                                @if($memory->isPhoto())
                                    <div class="aspect-square bg-gradient-to-br from-purple-100 to-pink-100 relative">
                                        <x-media.thumbnail :memory="$memory" :locked="$isDualLocked" />
                                    </div>
                                @elseif($memory->isVideo())
                                    <div class="aspect-video bg-gradient-to-br from-purple-100 to-pink-100 relative flex items-center justify-center">
                                        <div class="text-sm text-gray-700 font-semibold">
                                            {{ $isDualLocked ? 'Dual consent required' : ($memory->getThumbnailUrl() || $memory->getFileUrl() ? 'Video memory' : 'Video unavailable') }}
                                        </div>
                                    </div>
                                @elseif($memory->isVoiceNote())
                                    <div class="aspect-video bg-gradient-to-br from-purple-100 to-pink-100 flex items-center justify-center relative">
                                        <div class="text-sm text-gray-700 font-semibold">
                                            {{ $isDualLocked ? 'Dual consent required' : ($memory->getFileUrl() ? 'Voice memory' : 'Voice unavailable') }}
                                        </div>
                                    </div>
                                @else
                                    <div class="p-6 bg-gradient-to-br from-purple-100 to-pink-100 min-h-[200px] relative">
                                        <p class="text-gray-800 line-clamp-4">{{ $isDualLocked ? 'This memory is waiting for both approvals.' : $memory->description }}</p>
                                    </div>
                                @endif

                                <div class="p-4">
                                    @if($memory->title)
                                        <h3 class="font-bold text-gray-800 mb-2">{{ $memory->title }}</h3>
                                    @endif
                                    <div class="flex items-center justify-between text-sm text-gray-600">
                                        <span>{{ $memory->created_at->diffForHumans() }}</span>
                                        <span>{{ $memory->creator->name }}</span>
                                    </div>
                                    @if($memory->comfort)
                                        <div class="mt-2 text-xs font-semibold text-rose-700">Comfort</div>
                                    @endif
                                    @if($memory->isDual() && $isDualLocked)
                                        <div class="mt-2 text-xs font-semibold text-yellow-700">Waiting for both approvals</div>
                                    @endif
                                    @if($memory->reactions->count() > 0)
                                        <div class="mt-2 flex gap-1">
                                            @foreach($memory->reactions as $reaction)
                                                <span class="text-lg">{{ $reaction->getEmoji() }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </a>

                            <div class="absolute bottom-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity">
                                @can('toggleComfort', $memory)
                                    <button wire:click="toggleComfort({{ $memory->id }})"
                                        class="p-2 bg-rose-500 hover:bg-rose-600 text-white rounded-lg shadow-lg text-sm mr-1">
                                        {{ $memory->comfort ? 'Unset Comfort' : 'Set Comfort' }}
                                    </button>
                                @endcan
                                @if($memory->created_by === auth()->id() && !$memory->isLocked())
                                    <button wire:click="deleteMemory({{ $memory->id }})"
                                        wire:confirm="Are you sure you want to delete this memory?"
                                        class="p-2 bg-red-500 hover:bg-red-600 text-white rounded-lg shadow-lg text-sm">
                                        Delete
                                    </button>
                                @endif
                                @can('approveUnlock', $memory)
                                    <button wire:click="unlockMemory({{ $memory->id }})"
                                        class="p-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg shadow-lg text-sm mt-1">
                                        Approve Unlock
                                    </button>
                                @endcan
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-20">
                    <h2 class="text-3xl font-bold text-gray-800 mb-4">
                        {{ $showLocked ? 'No Dual Memories Yet' : 'No Memories Yet' }}
                    </h2>
                    <p class="text-gray-600 mb-8">Start preserving your special moments together</p>
                    <a href="/vault/upload"
                        class="inline-block px-8 py-4 bg-gradient-to-r from-purple-500 to-pink-500 text-white font-bold rounded-2xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all">
                        + Add Your First Memory
                    </a>
                </div>
            @endif
        @else
            <div class="bg-white/90 backdrop-blur-lg rounded-3xl shadow-2xl p-8 border border-white/20 text-center">
                <h2 class="text-3xl font-bold text-gray-800 mb-4">No Couple Found</h2>
                <p class="text-gray-600 mb-8">You need to be in a couple to access the vault</p>
                <a href="/couple/create-or-join"
                    class="inline-block px-8 py-4 bg-gradient-to-r from-purple-500 to-pink-500 text-white font-bold rounded-2xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all">
                    Create or Join Couple
                </a>
            </div>
        @endif
    </div>
</div>

