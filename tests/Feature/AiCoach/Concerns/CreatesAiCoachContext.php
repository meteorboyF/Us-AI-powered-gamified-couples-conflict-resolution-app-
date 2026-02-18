<?php

namespace Tests\Feature\AiCoach\Concerns;

use App\Models\Couple;
use App\Models\CoupleMember;
use App\Models\User;
use Illuminate\Support\Facades\Config;

trait CreatesAiCoachContext
{
    /**
     * @return array{0: \App\Models\Couple, 1: \App\Models\User}
     */
    private function createCoupleWithPartner(User $owner): array
    {
        $partner = User::factory()->create();

        $couple = Couple::query()->create([
            'name' => 'AI Coach Couple',
            'invite_code' => 'AIC'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT),
            'created_by_user_id' => $owner->id,
        ]);

        CoupleMember::query()->create([
            'couple_id' => $couple->id,
            'user_id' => $owner->id,
            'role' => 'owner',
            'joined_at' => now(),
        ]);

        CoupleMember::query()->create([
            'couple_id' => $couple->id,
            'user_id' => $partner->id,
            'role' => 'member',
            'joined_at' => now(),
        ]);

        $owner->forceFill(['current_couple_id' => $couple->id])->save();
        $partner->forceFill(['current_couple_id' => $couple->id])->save();

        return [$couple, $partner];
    }

    private function forceFakeProvider(): void
    {
        Config::set('us.ai.default_provider', 'fake');
        Config::set('us.features.ai_coach_v1', true);
    }
}
