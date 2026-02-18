<?php

namespace Tests\Feature\Vault;

use App\Models\User;
use App\Models\VaultItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Vault\Concerns\CreatesVaultContext;
use Tests\TestCase;

class VaultAuthzTest extends TestCase
{
    use CreatesVaultContext;
    use RefreshDatabase;

    public function test_non_member_gets_forbidden_on_vault_access(): void
    {
        $member = User::factory()->create();
        [$couple] = $this->createCoupleWithPartner($member);

        $item = VaultItem::query()->create([
            'couple_id' => $couple->id,
            'created_by_user_id' => $member->id,
            'type' => 'note',
            'title' => 'Private',
            'body' => 'Hidden',
        ]);

        $intruder = User::factory()->create();
        $intruder->forceFill(['current_couple_id' => $couple->id])->save();

        $this->actingAs($intruder)->getJson('/vault')->assertForbidden();
        $this->actingAs($intruder)->getJson('/vault/'.$item->id)->assertForbidden();
    }

    public function test_no_current_couple_returns_conflict(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->getJson('/vault')->assertStatus(409);
    }
}
