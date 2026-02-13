<?php

namespace App\Providers;

use App\Models\AiBridgeSuggestion;
use App\Models\Memory;
use App\Policies\AiBridgeSuggestionPolicy;
use App\Policies\MemoryPolicy;
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
    }
}
