<?php

namespace App\Providers;

use App\Models\Chat;
use App\Models\ChatMessage;
use App\Policies\ChatMessagePolicy;
use App\Policies\ChatPolicy;
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
        Gate::policy(Chat::class, ChatPolicy::class);
        Gate::policy(ChatMessage::class, ChatMessagePolicy::class);
    }
}
