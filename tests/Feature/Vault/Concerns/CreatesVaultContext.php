<?php

namespace Tests\Feature\Vault\Concerns;

use App\Models\Couple;
use App\Models\CoupleMember;
use App\Models\User;

trait CreatesVaultContext
{
    /**
     * @return array{0: \App\Models\Couple, 1: \App\Models\User}
     */
    private function createCoupleWithPartner(User $owner): array
    {
        $partner = User::factory()->create();

        $couple = Couple::query()->create([
            'name' => 'Vault Couple',
            'invite_code' => 'VLT'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT),
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
}
