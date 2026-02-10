<div class="min-h-screen bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50">
    <div class="container mx-auto px-4 py-8">
        
        @if($couple && $world)
            <!-- Header with World Info -->
            <div class="mb-8 text-center">
                <div class="inline-block p-8 bg-white/80 backdrop-blur-lg rounded-3xl shadow-2xl border border-white/20">
                    <h1 class="text-4xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent mb-2">
                        Your World Together
                    </h1>
                    <p class="text-gray-600">{{ ucfirst($world->theme_type) }} Theme</p>
                </div>
            </div>

            <!-- Level & XP Card -->
            <div class="max-w-4xl mx-auto mb-8">
                <div class="bg-gradient-to-r from-purple-500 via-pink-500 to-orange-500 p-1 rounded-3xl shadow-2xl">
                    <div class="bg-white/95 backdrop-blur-lg rounded-3xl p-8">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h2 class="text-3xl font-bold text-gray-800">Level {{ $world->level }}</h2>
                                <p class="text-gray-600">{{ number_format($world->xp_total) }} Total XP</p>
                            </div>
                            <div class="text-right">
                                <div class="text-4xl font-bold bg-gradient-to-r from-green-500 to-emerald-500 bg-clip-text text-transparent">
                                    +{{ $todayXp }} XP
                                </div>
                                <p class="text-sm text-gray-600">Today</p>
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div class="relative">
                            <div class="h-6 bg-gray-200 rounded-full overflow-hidden">
                                <div 
                                    class="h-full bg-gradient-to-r from-purple-500 via-pink-500 to-orange-500 transition-all duration-1000 ease-out rounded-full"
                                    style="width: {{ $levelProgress }}%">
                                </div>
                            </div>
                            <p class="text-center text-sm text-gray-600 mt-2">
                                {{ number_format($xpForNextLevel - ($world->xp_total % $xpForNextLevel)) }} XP to Level {{ $world->level + 1 }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="max-w-4xl mx-auto mb-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Mood Check-in -->
                    <a href="/checkin" class="group">
                        <div class="bg-white/80 backdrop-blur-lg rounded-2xl p-6 shadow-lg hover:shadow-2xl transform hover:scale-105 transition-all duration-300 border border-white/20">
                            <div class="text-5xl mb-4 group-hover:scale-110 transition-transform">😊</div>
                            <h3 class="text-xl font-bold text-gray-800 mb-2">Daily Check-in</h3>
                            <p class="text-gray-600 text-sm">Share how you're feeling</p>
                            <div class="mt-4 text-purple-600 font-semibold">+10 XP</div>
                        </div>
                    </a>

                    <!-- Missions -->
                    <a href="/missions" class="group">
                        <div class="bg-white/80 backdrop-blur-lg rounded-2xl p-6 shadow-lg hover:shadow-2xl transform hover:scale-105 transition-all duration-300 border border-white/20">
                            <div class="text-5xl mb-4 group-hover:scale-110 transition-transform">🎯</div>
                            <h3 class="text-xl font-bold text-gray-800 mb-2">Missions</h3>
                            <p class="text-gray-600 text-sm">Complete daily tasks</p>
                            <div class="mt-4 text-pink-600 font-semibold">Up to 75 XP</div>
                        </div>
                    </a>

                    <!-- Chat -->
                    <a href="/chat" class="group">
                        <div class="bg-white/80 backdrop-blur-lg rounded-2xl p-6 shadow-lg hover:shadow-2xl transform hover:scale-105 transition-all duration-300 border border-white/20">
                            <div class="text-5xl mb-4 group-hover:scale-110 transition-transform">💬</div>
                            <h3 class="text-xl font-bold text-gray-800 mb-2">Chat</h3>
                            <p class="text-gray-600 text-sm">Talk with your partner</p>
                            <div class="mt-4 text-blue-600 font-semibold">Coming Soon</div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Recent XP Events -->
            @if($recentXpEvents && $recentXpEvents->count() > 0)
                <div class="max-w-4xl mx-auto">
                    <div class="bg-white/80 backdrop-blur-lg rounded-3xl shadow-2xl p-8 border border-white/20">
                        <h3 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-3">
                            <span class="text-3xl">⭐</span>
                            Recent Activity
                        </h3>
                        <div class="space-y-3">
                            @foreach($recentXpEvents as $event)
                                <div class="flex items-center justify-between p-4 bg-gradient-to-r from-purple-50 to-pink-50 rounded-xl hover:shadow-md transition-shadow">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-full bg-gradient-to-r from-purple-500 to-pink-500 flex items-center justify-center text-white font-bold">
                                            @switch($event->type)
                                                @case('checkin') 😊 @break
                                                @case('mission') 🎯 @break
                                                @case('repair') 💚 @break
                                                @case('vault') 📸 @break
                                                @case('chat') 💬 @break
                                                @default ⭐
                                            @endswitch
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-800 capitalize">{{ str_replace('_', ' ', $event->type) }}</p>
                                            <p class="text-sm text-gray-600">{{ $event->created_at->diffForHumans() }}</p>
                                        </div>
                                    </div>
                                    <div class="text-2xl font-bold bg-gradient-to-r from-green-500 to-emerald-500 bg-clip-text text-transparent">
                                        +{{ $event->xp_amount }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

        @else
            <!-- No Couple Yet -->
            <div class="max-w-2xl mx-auto text-center">
                <div class="bg-white/80 backdrop-blur-lg rounded-3xl shadow-2xl p-12 border border-white/20">
                    <div class="text-8xl mb-6">💕</div>
                    <h2 class="text-3xl font-bold text-gray-800 mb-4">Welcome to Us!</h2>
                    <p class="text-gray-600 mb-8">Create or join a couple to start your journey together</p>
                    <a href="/couple/create-or-join" class="inline-block px-8 py-4 bg-gradient-to-r from-purple-500 to-pink-500 text-white font-bold rounded-2xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all">
                        Get Started
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
