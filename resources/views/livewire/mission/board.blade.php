<div class="min-h-screen bg-gradient-to-br from-orange-50 via-pink-50 to-purple-50">
    <div class="container mx-auto px-4 py-8">
        
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold bg-gradient-to-r from-orange-500 via-pink-500 to-purple-500 bg-clip-text text-transparent mb-2">
                Today's Missions
            </h1>
            <p class="text-gray-600">Complete tasks to strengthen your relationship and earn XP</p>
        </div>

        @if($couple && $missions && $missions->count() > 0)
            <!-- Missions Grid -->
            <div class="max-w-4xl mx-auto space-y-6">
                @foreach($missions as $assignment)
                    <div class="bg-white/80 backdrop-blur-lg rounded-3xl shadow-xl p-8 border border-white/20 hover:shadow-2xl transition-all duration-300">
                        <div class="flex items-start justify-between gap-6">
                            <!-- Mission Icon & Info -->
                            <div class="flex-1">
                                <div class="flex items-center gap-4 mb-4">
                                    <!-- Category Icon -->
                                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-r 
                                        @switch($assignment->mission->category)
                                            @case('gratitude') from-yellow-400 to-orange-400 @break
                                            @case('communication') from-blue-400 to-indigo-400 @break
                                            @case('affection') from-pink-400 to-red-400 @break
                                            @case('quality_time') from-purple-400 to-pink-400 @break
                                            @case('memories') from-green-400 to-emerald-400 @break
                                            @case('growth') from-indigo-400 to-purple-400 @break
                                            @case('repair') from-teal-400 to-cyan-400 @break
                                            @default from-gray-400 to-gray-500
                                        @endswitch
                                        flex items-center justify-center text-3xl shadow-lg">
                                        @switch($assignment->mission->category)
                                            @case('gratitude') 🙏 @break
                                            @case('communication') 💬 @break
                                            @case('affection') 💕 @break
                                            @case('quality_time') ⏰ @break
                                            @case('memories') 📸 @break
                                            @case('growth') 🌱 @break
                                            @case('repair') 💚 @break
                                            @default 🎯
                                        @endswitch
                                    </div>

                                    <div class="flex-1">
                                        <h3 class="text-2xl font-bold text-gray-800 mb-2">
                                            {{ $assignment->mission->title }}
                                        </h3>
                                        <p class="text-gray-600">
                                            {{ $assignment->mission->description }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Mission Type & Category Tags -->
                                <div class="flex gap-2 mb-4">
                                    <span class="px-4 py-2 bg-purple-100 text-purple-700 rounded-full text-sm font-semibold capitalize">
                                        {{ $assignment->mission->type }}
                                    </span>
                                    <span class="px-4 py-2 bg-blue-100 text-blue-700 rounded-full text-sm font-semibold capitalize">
                                        {{ str_replace('_', ' ', $assignment->mission->category) }}
                                    </span>
                                </div>

                                <!-- Completion Status -->
                                @if($assignment->completions->where('user_id', auth()->id())->count() > 0)
                                    <div class="flex items-center gap-3 p-4 bg-green-50 border-2 border-green-200 rounded-xl">
                                        <span class="text-3xl">✅</span>
                                        <div>
                                            <p class="font-semibold text-green-700">Completed!</p>
                                            <p class="text-sm text-green-600">
                                                {{ $assignment->completions->where('user_id', auth()->id())->first()->created_at->diffForHumans() }}
                                            </p>
                                        </div>
                                    </div>
                                @else
                                    <button 
                                        wire:click="completeMission({{ $assignment->id }})"
                                        class="px-8 py-4 bg-gradient-to-r from-orange-500 via-pink-500 to-purple-500 text-white font-bold rounded-2xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300">
                                        Complete Mission
                                    </button>
                                @endif
                            </div>

                            <!-- XP Reward Badge -->
                            <div class="text-center">
                                <div class="w-24 h-24 rounded-2xl bg-gradient-to-r from-yellow-400 to-orange-400 flex items-center justify-center shadow-lg transform rotate-3 hover:rotate-0 transition-transform">
                                    <div>
                                        <div class="text-3xl font-bold text-white">+{{ $assignment->mission->xp_reward }}</div>
                                        <div class="text-xs font-semibold text-white/90">XP</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if (session()->has('message'))
                <div class="max-w-4xl mx-auto mt-6">
                    <div class="p-6 bg-green-50 border-2 border-green-200 text-green-700 rounded-2xl text-center font-semibold text-lg animate-bounce-in">
                        {{ session('message') }}
                    </div>
                </div>
            @endif

        @else
            <!-- No Missions -->
            <div class="max-w-2xl mx-auto text-center">
                <div class="bg-white/80 backdrop-blur-lg rounded-3xl shadow-2xl p-12 border border-white/20">
                    <div class="text-8xl mb-6">🎯</div>
                    <h2 class="text-3xl font-bold text-gray-800 mb-4">No Missions Yet</h2>
                    <p class="text-gray-600 mb-8">Missions will be assigned daily. Check back tomorrow!</p>
                </div>
            </div>
        @endif

        <!-- Info Card -->
        <div class="max-w-4xl mx-auto mt-8">
            <div class="p-6 bg-orange-50/80 backdrop-blur-lg rounded-2xl border border-orange-200">
                <p class="text-sm text-gray-700 text-center">
                    💡 <strong>Tip:</strong> New missions are assigned every day. Complete them to earn XP and level up your world!
                </p>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes bounce-in {
        0% { opacity: 0; transform: scale(0.9); }
        50% { transform: scale(1.05); }
        100% { opacity: 1; transform: scale(1); }
    }
    .animate-bounce-in { animation: bounce-in 0.6s ease-out; }
</style>
