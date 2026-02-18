<?php

namespace App\Providers;

use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\Couple;
use App\Models\CoupleMember;
use App\Models\CoupleMission;
use App\Models\CoupleWorldItem;
use App\Models\CoupleWorldState;
use App\Models\DailyCheckin;
use App\Models\VaultItem;
use App\Models\VaultUnlockRequest;
use App\Policies\ChatMessagePolicy;
use App\Policies\ChatPolicy;
use App\Policies\CoupleMemberPolicy;
use App\Policies\CoupleMissionPolicy;
use App\Policies\CouplePolicy;
use App\Policies\CoupleWorldItemPolicy;
use App\Policies\CoupleWorldStatePolicy;
use App\Policies\DailyCheckinPolicy;
use App\Policies\VaultItemPolicy;
use App\Policies\VaultUnlockRequestPolicy;
use App\Services\AI\AiManager;
use App\Services\AI\Contracts\AiProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AiProvider::class, function ($app) {
            return $app->make(AiManager::class)->provider();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Chat::class, ChatPolicy::class);
        Gate::policy(ChatMessage::class, ChatMessagePolicy::class);
        Gate::policy(Couple::class, CouplePolicy::class);
        Gate::policy(CoupleMember::class, CoupleMemberPolicy::class);
        Gate::policy(CoupleMission::class, CoupleMissionPolicy::class);
        Gate::policy(CoupleWorldState::class, CoupleWorldStatePolicy::class);
        Gate::policy(CoupleWorldItem::class, CoupleWorldItemPolicy::class);
        Gate::policy(DailyCheckin::class, DailyCheckinPolicy::class);
        Gate::policy(VaultItem::class, VaultItemPolicy::class);
        Gate::policy(VaultUnlockRequest::class, VaultUnlockRequestPolicy::class);

        RateLimiter::for('chat-send', function (Request $request) {
            $userKey = $request->user()?->id ? 'user-'.$request->user()->id : 'ip-'.$request->ip();

            return [
                Limit::perMinute((int) config('us.chat.rate_limit_per_minute', 20))->by($userKey),
            ];
        });
    }
}
