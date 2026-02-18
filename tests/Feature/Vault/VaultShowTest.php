<?php

namespace Tests\Feature\Vault;

use App\Models\User;
use App\Models\VaultItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Vault\Concerns\CreatesVaultContext;
use Tests\TestCase;

class VaultShowTest extends TestCase
{
    use CreatesVaultContext;
    use RefreshDatabase;

    public function test_show_returns_redacted_for_sensitive_item_without_unlock(): void
    {
        $user = User::factory()->create();
        [$couple] = $this->createCoupleWithPartner($user);

        $item = VaultItem::query()->create([
            'couple_id' => $couple->id,
            'created_by_user_id' => $user->id,
            'type' => 'timeline',
            'title' => 'Sensitive timeline',
            'body' => 'Hidden details',
            'is_sensitive' => true,
            'is_locked' => true,
            'meta' => ['consent_required' => true],
        ]);

        $this->actingAs($user)
            ->getJson('/vault/'.$item->id)
            ->assertOk()
            ->assertJsonPath('item.redacted', true)
            ->assertJsonPath('item.body', null);
    }
}
