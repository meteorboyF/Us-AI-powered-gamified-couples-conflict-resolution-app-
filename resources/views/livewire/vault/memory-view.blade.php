<div class="min-h-screen bg-gradient-to-br from-purple-50 via-pink-50 to-rose-50 py-12 px-4">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white/90 backdrop-blur-lg rounded-3xl shadow-2xl overflow-hidden border border-white/20">
            <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">{{ $memory->title ?? 'Memory' }}</h2>
                    <p class="text-sm text-gray-600">{{ $memory->creator->name }} - {{ $memory->created_at->format('M d, Y') }}</p>
                </div>
                <a href="/vault" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">
                    Back
                </a>
            </div>

            @if($memory->isDual())
                <div class="px-8 pt-8">
                    <div class="p-4 rounded-xl border border-yellow-200 bg-yellow-50">
                        <div class="font-semibold text-yellow-800">Dual-consent unlock</div>
                        <div class="text-sm text-yellow-700 mt-1">
                            @if($unlockStatus['is_unlocked'] ?? false)
                                Unlocked for both partners
                                @if(!empty($unlockStatus['expires_at']))
                                    until {{ $unlockStatus['expires_at']->format('H:i') }}.
                                @endif
                            @else
                                Waiting for both partners to approve unlock.
                            @endif
                        </div>

                        <div class="mt-3 space-y-1 text-sm text-gray-700">
                            @foreach($memory->couple->users as $member)
                                @php
                                    $approved = $unlockStatus['approvals'][$member->id]['approved'] ?? false;
                                @endphp
                                <div>{{ $member->name }}: {{ $approved ? 'Approved' : 'Pending' }}</div>
                            @endforeach
                        </div>

                        @can('approveUnlock', $memory)
                            <button wire:click="approveUnlock"
                                class="mt-4 px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg text-sm font-semibold">
                                Approve Unlock
                            </button>
                        @endcan
                    </div>
                </div>
            @endif

            <div class="p-8">
                @if($canViewContent)
                    @if($memory->isPhoto())
                        @if($memory->getFileUrl())
                            <img src="{{ $memory->getFileUrl() }}" alt="{{ $memory->title }}" class="w-full rounded-2xl shadow-lg">
                        @else
                            <div class="bg-gray-100 rounded-2xl p-8 text-center text-gray-700">
                                Memory file is unavailable.
                            </div>
                        @endif
                    @elseif($memory->isVideo())
                        @if($memory->getFileUrl())
                            <video controls class="w-full rounded-2xl shadow-lg">
                                <source src="{{ $memory->getFileUrl() }}" type="{{ $memory->mime_type }}">
                            </video>
                        @else
                            <div class="bg-gray-100 rounded-2xl p-8 text-center text-gray-700">
                                Memory file is unavailable.
                            </div>
                        @endif
                    @elseif($memory->isVoiceNote())
                        <div class="bg-gradient-to-br from-purple-100 to-pink-100 rounded-2xl p-12 text-center">
                            @if($memory->getFileUrl())
                                <audio controls class="w-full">
                                    <source src="{{ $memory->getFileUrl() }}" type="{{ $memory->mime_type }}">
                                </audio>
                            @else
                                <div class="text-gray-700">Memory file is unavailable.</div>
                            @endif
                        </div>
                    @else
                        <div class="bg-gradient-to-br from-purple-100 to-pink-100 rounded-2xl p-8">
                            <p class="text-gray-800 text-lg whitespace-pre-wrap">{{ $memory->description }}</p>
                        </div>
                    @endif

                    @if($memory->description && !$memory->isText())
                        <div class="mt-6 p-6 bg-gray-50 rounded-2xl">
                            <p class="text-gray-800">{{ $memory->description }}</p>
                        </div>
                    @endif
                @else
                    <div class="bg-gray-100 rounded-2xl p-8 text-center text-gray-700">
                        Content is locked until both partners approve unlock.
                    </div>
                @endif
            </div>

            @if($canViewContent)
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
            @endif

            <div class="px-8 pb-8 flex gap-3">
                @can('toggleComfort', $memory)
                    <button wire:click="toggleComfort"
                        class="px-4 py-3 bg-rose-500 hover:bg-rose-600 text-white font-semibold rounded-xl transition-colors">
                        {{ $memory->comfort ? 'Unset Comfort' : 'Set Comfort' }}
                    </button>
                @endcan

                @if($memory->created_by === auth()->id())
                    @if(!$memory->isLocked())
                        <button wire:click="lockMemory"
                            class="flex-1 py-3 bg-gradient-to-r from-yellow-500 to-amber-500 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all">
                            Lock as Dual (+10 XP)
                        </button>
                        <button wire:click="deleteMemory" wire:confirm="Are you sure you want to delete this memory?"
                            class="px-6 py-3 bg-red-500 hover:bg-red-600 text-white font-bold rounded-xl transition-colors">
                            Delete
                        </button>
                    @else
                        <div class="flex-1 py-3 bg-gradient-to-r from-yellow-500 to-amber-500 text-white font-bold rounded-xl text-center">
                            Dual-Consent Memory
                        </div>
                    @endif
                @endif
            </div>

            @if (session()->has('message'))
                <div class="mx-8 mb-8 p-4 bg-green-50 border-2 border-green-200 text-green-700 rounded-xl text-center">
                    {{ session('message') }}
                </div>
            @endif

            @if (session()->has('error'))
                <div class="mx-8 mb-8 p-4 bg-red-50 border-2 border-red-200 text-red-700 rounded-xl text-center">
                    {{ session('error') }}
                </div>
            @endif
        </div>
    </div>
</div>

