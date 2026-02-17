<?php

namespace App\Providers;

use App\Models\Couple;
use App\Models\CoupleMember;
use App\Policies\CoupleMemberPolicy;
use App\Policies\CouplePolicy;
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
        Gate::policy(Couple::class, CouplePolicy::class);
        Gate::policy(CoupleMember::class, CoupleMemberPolicy::class);
    }
}
