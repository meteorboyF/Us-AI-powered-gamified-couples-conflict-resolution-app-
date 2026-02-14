<?php

namespace App\Providers;

use App\Models\AiBridgeSuggestion;
use App\Models\ChatV2\Conversation as ChatV2Conversation;
use App\Models\Memory;
use App\Models\World;
use App\Models\WorldItem;
use App\Policies\AiBridgeSuggestionPolicy;
use App\Policies\ChatV2\ConversationPolicy as ChatV2ConversationPolicy;
use App\Policies\MemoryPolicy;
use App\Policies\WorldItemPolicy;
use App\Policies\WorldPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Memory::class, MemoryPolicy::class);
        Gate::policy(AiBridgeSuggestion::class, AiBridgeSuggestionPolicy::class);
        Gate::policy(World::class, WorldPolicy::class);
        Gate::policy(WorldItem::class, WorldItemPolicy::class);
        Gate::policy(ChatV2Conversation::class, ChatV2ConversationPolicy::class);
    }
}
