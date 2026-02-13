<div class="relative" wire:poll.10s="loadUnreadCount">
    <!-- Bell Icon Button -->
    <button 
        wire:click="toggleDropdown"
        class="relative p-3 rounded-full hover:bg-gray-100 transition-colors">
        <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
        </svg>
        
        <!-- Unread Badge -->
        @if($unreadCount > 0)
            <span class="absolute top-1 right-1 flex h-5 w-5">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-5 w-5 bg-red-500 text-white text-xs items-center justify-center font-bold">
                    {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                </span>
            </span>
        @endif
    </button>

    <!-- Dropdown -->
    @if($showDropdown)
        <div class="absolute right-0 mt-2 w-96 bg-white/95 backdrop-blur-lg rounded-2xl shadow-2xl border border-white/20 z-50 max-h-96 overflow-hidden">
            <!-- Header -->
            <div class="p-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="font-bold text-gray-800">Notifications</h3>
                @if($unreadCount > 0)
                    <button 
                        wire:click="markAllAsRead"
                        class="text-sm text-purple-600 hover:text-purple-700 font-semibold">
                        Mark all read
                    </button>
                @endif
            </div>

            <!-- Notifications List -->
            <div class="overflow-y-auto max-h-80">
                @forelse($notifications ?? [] as $notification)
                    <div 
                        wire:click="markAsRead({{ $notification->id }})"
                        class="p-4 border-b border-gray-100 hover:bg-purple-50 transition-colors cursor-pointer">
                        <div class="flex items-start gap-3">
                            <!-- Icon based on type -->
                            <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center text-2xl
                                @switch($notification->type)
                                    @case('mood_alert') bg-blue-100 @break
                                    @case('mission_complete') bg-green-100 @break
                                    @case('level_up') bg-yellow-100 @break
                                    @case('love_button') bg-pink-100 @break
                                    @default bg-gray-100
                                @endswitch">
                                @switch($notification->type)
                                    @case('mood_alert') 😢 @break
                                    @case('mission_complete') 🎯 @break
                                    @case('level_up') 🎉 @break
                                    @case('love_button') ❤️ @break
                                    @default 🔔
                                @endswitch
                            </div>

                            <!-- Content -->
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-gray-800 text-sm">{{ $notification->title }}</p>
                                <p class="text-gray-600 text-sm mt-1">{{ $notification->message }}</p>
                                <p class="text-xs text-gray-500 mt-2">{{ $notification->created_at->diffForHumans() }}</p>
                            </div>

                            <!-- Unread indicator -->
                            @if($notification->isUnread())
                                <div class="flex-shrink-0 w-2 h-2 bg-purple-500 rounded-full"></div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-12 text-center">
                        <div class="text-6xl mb-4">🔔</div>
                        <p class="text-gray-600">No notifications yet</p>
                        <p class="text-sm text-gray-500 mt-2">We'll notify you about important updates</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Click outside to close -->
        <div 
            wire:click="toggleDropdown"
            class="fixed inset-0 z-40"></div>
    @endif
    <style>
        @keyframes ping {
            75%, 100% {
                transform: scale(2);
                opacity: 0;
            }
        }

        .animate-ping {
            animation: ping 1s cubic-bezier(0, 0, 0.2, 1) infinite;
        }
    </style>
</div>
