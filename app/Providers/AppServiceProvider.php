<?php

namespace App\Providers;

use App\Models\Couple;
use App\Models\CoupleMember;
use App\Models\CoupleWorldItem;
use App\Models\CoupleWorldState;
use App\Policies\CoupleMemberPolicy;
use App\Policies\CouplePolicy;
use App\Policies\CoupleWorldItemPolicy;
use App\Policies\CoupleWorldStatePolicy;
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
        Gate::policy(CoupleWorldState::class, CoupleWorldStatePolicy::class);
        Gate::policy(CoupleWorldItem::class, CoupleWorldItemPolicy::class);
    }
}
