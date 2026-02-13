<div class="min-h-screen bg-gradient-to-br from-teal-50 via-blue-50 to-indigo-50">
    <div class="container mx-auto px-4 py-8">
        
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold bg-gradient-to-r from-teal-500 via-blue-500 to-indigo-500 bg-clip-text text-transparent mb-2">
                Partner's Mood
            </h1>
            <p class="text-gray-600">See how your partner is feeling today</p>
        </div>

        <!-- Partner Mood Card -->
        <div class="max-w-2xl mx-auto">
            <div class="bg-white/80 backdrop-blur-lg rounded-3xl shadow-2xl p-8 border border-white/20">
                
                <!-- Mood Display -->
                <div class="text-center mb-8">
                    <div class="text-8xl mb-4">
                        @if($partnerMood)
                            @switch($partnerMood['mood_level'])
                                @case(1) 😢 @break
                                @case(2) 😕 @break
                                @case(3) 😐 @break
                                @case(4) 😊 @break
                                @case(5) 😄 @break
                            @endswitch
                        @else
                            😶
                        @endif
                    </div>
                    
                    @if($partnerMood)
                        <h2 class="text-3xl font-bold text-gray-800 mb-2">
                            @switch($partnerMood['mood_level'])
                                @case(1) Feeling Down @break
                                @case(2) A Bit Low @break
                                @case(3) Okay @break
                                @case(4) Good @break
                                @case(5) Great! @break
                            @endswitch
                        </h2>
                        <p class="text-gray-600">Checked in {{ $partnerMood['checked_in_at'] }}</p>
                    @else
                        <h2 class="text-3xl font-bold text-gray-800 mb-2">No Check-in Today</h2>
                        <p class="text-gray-600">Your partner hasn't checked in yet</p>
                    @endif
                </div>

                @if($partnerMood)
                    <!-- Needs -->
                    @if(!empty($partnerMood['needs']) && count($partnerMood['needs']) > 0)
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 text-center">What They Need</h3>
                            <div class="flex flex-wrap justify-center gap-3">
                                @foreach($partnerMood['needs'] as $need)
                                    <span class="px-6 py-3 bg-gradient-to-r from-pink-500 to-orange-500 text-white rounded-full font-semibold shadow-lg">
                                        {{ ucfirst(str_replace('_', ' ', $need)) }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Supportive Suggestions -->
                    <div class="p-6 bg-gradient-to-r from-teal-50 to-blue-50 rounded-2xl border-2 border-teal-200">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                            <span class="text-2xl">💡</span>
                            How You Can Help
                        </h3>
                        <ul class="space-y-2 text-gray-700">
                            @if(in_array('space', $partnerMood['needs'] ?? []))
                                <li class="flex items-start gap-2">
                                    <span class="text-green-500 font-bold">•</span>
                                    Give them some alone time to recharge
                                </li>
                            @endif
                            @if(in_array('talk', $partnerMood['needs'] ?? []))
                                <li class="flex items-start gap-2">
                                    <span class="text-green-500 font-bold">•</span>
                                    Ask if they'd like to talk about what's on their mind
                                </li>
                            @endif
                            @if(in_array('reassurance', $partnerMood['needs'] ?? []))
                                <li class="flex items-start gap-2">
                                    <span class="text-green-500 font-bold">•</span>
                                    Remind them how much you care and appreciate them
                                </li>
                            @endif
                            @if(in_array('help', $partnerMood['needs'] ?? []))
                                <li class="flex items-start gap-2">
                                    <span class="text-green-500 font-bold">•</span>
                                    Offer to help with tasks or responsibilities
                                </li>
                            @endif
                            @if(in_array('affection', $partnerMood['needs'] ?? []))
                                <li class="flex items-start gap-2">
                                    <span class="text-green-500 font-bold">•</span>
                                    Show physical affection - a hug, kiss, or cuddle
                                </li>
                            @endif
                            @if($partnerMood['mood_level'] <= 2)
                                <li class="flex items-start gap-2">
                                    <span class="text-green-500 font-bold">•</span>
                                    Be patient and understanding - they might need extra support
                                </li>
                            @endif
                        </ul>
                    </div>
                @endif
            </div>

            <!-- Privacy Note -->
            <div class="mt-6 p-6 bg-blue-50/80 backdrop-blur-lg rounded-2xl border border-blue-200">
                <p class="text-sm text-gray-700 text-center">
                    🔒 <strong>Privacy:</strong> Detailed notes are kept private. You only see their mood level and needs.
                </p>
            </div>
        </div>
    </div>
</div>
