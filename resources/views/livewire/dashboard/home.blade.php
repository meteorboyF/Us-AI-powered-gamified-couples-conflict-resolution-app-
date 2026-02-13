<div class="py-12 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

        <!-- Welcome Section -->
        <div class="mb-8 px-4 sm:px-0">
            <h1 class="text-3xl font-bold text-gray-800">
                Hi, {{ $user->name }}! 👋
            </h1>
            <p class="text-gray-600 mt-2">
                @if($couple)
                    Building a stronger bond with <span
                        class="font-semibold text-purple-600">{{ $partnerName ?: 'your partner' }}</span>.
                @else
                    Let's get you connected to your partner.
                @endif
            </p>
        </div>

        @if(!$couple)
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 text-center">
                <h3 class="text-lg font-bold text-gray-800">Partner Connection Needed</h3>
                <p class="mb-4">You need to link with your partner to use the dashboard features.</p>
                @livewire('couple.create-or-join')
            </div>
        @else

            <!-- Stats Grid -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8 px-4 sm:px-0">
                <!-- Level Card -->
                <div
                    class="bg-gradient-to-br from-purple-500 to-indigo-600 rounded-2xl p-6 text-white shadow-lg transform hover:scale-105 transition-transform">
                    <div class="text-purple-100 text-xs font-bold uppercase tracking-wider mb-1">Level {{ $stats['level'] }}
                    </div>
                    <div class="text-3xl font-bold">{{ $stats['xp'] }} <span
                            class="text-lg font-normal opacity-80">XP</span></div>
                    <div class="w-full bg-black/20 h-1 mt-3 rounded-full overflow-hidden">
                        <div class="bg-white/90 h-full rounded-full" style="width: {{ ($stats['xp'] % 1000) / 10 }}%"></div>
                    </div>
                </div>

                <!-- Streak Card -->
                <div
                    class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col justify-center items-center transform hover:scale-105 transition-transform">
                    <div class="text-4xl mb-2">🔥</div>
                    <div class="text-2xl font-bold text-gray-800">{{ $stats['streak'] }} Days</div>
                    <div class="text-gray-500 text-xs uppercase tracking-wider">Current Streak</div>
                </div>

                <!-- Memories Card -->
                <a href="{{ route('vault.gallery') }}"
                    class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col justify-center items-center transform hover:scale-105 transition-transform group hover:border-pink-200">
                    <div class="text-4xl mb-2 group-hover:scale-110 transition-transform">📸</div>
                    <div class="text-2xl font-bold text-gray-800">{{ $stats['memories'] }}</div>
                    <div class="text-gray-500 text-xs uppercase tracking-wider">Memories Saved</div>
                </a>

                <!-- Mood Card (Placeholder) -->
                <div
                    class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col justify-center items-center transform hover:scale-105 transition-transform">
                    <div class="text-4xl mb-2">😊</div>
                    <div class="text-gray-800 font-bold">Good</div>
                    <div class="text-gray-500 text-xs uppercase tracking-wider">Avg. Mood</div>
                </div>
            </div>

            <!-- Main Features Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 px-4 sm:px-0">

                <!-- Daily Mission -->
                <div class="md:col-span-2 bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden relative">
                    <div class="absolute top-0 right-0 p-4 opacity-10">
                        <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                    </div>

                    <div class="p-8">
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                            <div>
                                <h3 class="text-2xl font-bold text-gray-800 mb-2">Daily Mission</h3>
                                <p class="text-gray-600 max-w-lg mb-4">
                                    {{ $dailyMission->description ?? 'Check in with your partner and ask how their day was.' }}
                                </p>
                                <div class="flex items-center gap-2">
                                    <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-bold">
                                        +{{ $dailyMission->xp_reward ?? 50 }} XP
                                    </span>
                                    <span class="text-sm text-gray-500">Resets in 12h</span>
                                </div>
                            </div>

                            @if($missionCompleted)
                                <div class="bg-green-100 text-green-700 px-6 py-3 rounded-xl font-bold flex items-center gap-2">
                                    ✅ Complete
                                </div>
                            @else
                                <button wire:click="completeMission"
                                    class="bg-gray-900 text-white px-6 py-3 rounded-xl font-bold hover:bg-gray-800 transition-colors shadow-lg hover:shadow-xl">
                                    Mark Complete
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Quick Actions / Coach -->
                <div
                    class="bg-gradient-to-br from-teal-50 to-blue-50 rounded-3xl p-8 border border-teal-100 relative overflow-hidden group hover:shadow-md transition-all">
                    <div class="relative z-10">
                        <h3 class="text-xl font-bold text-gray-800 mb-2">AI Coach</h3>
                        <p class="text-gray-600 mb-6 text-sm">Need advice? Or just need to vent?</p>
                        <a href="{{ route('coach.chat') }}"
                            class="inline-block bg-white text-teal-600 px-6 py-2 rounded-xl font-bold shadow-sm hover:shadow-md transition-all">
                            Start Chat →
                        </a>
                    </div>
                    <div
                        class="absolute bottom-[-20px] right-[-20px] text-8xl opacity-20 transform group-hover:scale-110 transition-transform">
                        🤖
                    </div>
                </div>
            </div>

            <!-- Features Row -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 px-4 sm:px-0">
                <!-- Repair Conflict -->
                <a href="{{ route('repair.history') }}"
                    class="group bg-white p-6 rounded-3xl shadow-sm border border-gray-100 hover:border-purple-200 hover:shadow-md transition-all flex items-center">
                    <div
                        class="h-16 w-16 bg-purple-100 rounded-2xl flex items-center justify-center text-3xl mr-6 group-hover:scale-110 transition-transform">
                        🕊️
                    </div>
                    <div>
                        <h4 class="text-xl font-bold text-gray-800">Resolution Center</h4>
                        <p class="text-gray-500 text-sm mt-1">Resolve conflicts and build agreements.</p>
                    </div>
                </a>

                <!-- Vault -->
                <a href="{{ route('vault.gallery') }}"
                    class="group bg-white p-6 rounded-3xl shadow-sm border border-gray-100 hover:border-pink-200 hover:shadow-md transition-all flex items-center">
                    <div
                        class="h-16 w-16 bg-pink-100 rounded-2xl flex items-center justify-center text-3xl mr-6 group-hover:scale-110 transition-transform">
                        💎
                    </div>
                    <div>
                        <h4 class="text-xl font-bold text-gray-800">Memory Vault</h4>
                        <p class="text-gray-500 text-sm mt-1">Preserve your special moments.</p>
                    </div>
                </a>
            </div>

        @endif
    </div>
</div>
