<?php

namespace Tests\Feature\Vault;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Vault\Concerns\CreatesVaultContext;
use Tests\TestCase;

class VaultCreateTest extends TestCase
{
    use CreatesVaultContext;
    use RefreshDatabase;

    public function test_member_can_create_note_and_reason_items(): void
    {
        $user = User::factory()->create();
        [$couple] = $this->createCoupleWithPartner($user);

        $this->actingAs($user)
            ->postJson('/vault', [
                'type' => 'note',
                'title' => 'First memory',
                'body' => 'A good day',
            ])
            ->assertCreated()
            ->assertJsonPath('item.type', 'note');

        $this->actingAs($user)
            ->postJson('/vault', [
                'type' => 'reason',
                'title' => 'Reason list',
                'body' => 'You are patient.',
                'is_sensitive' => true,
                'meta' => ['consent_required' => true],
            ])
            ->assertCreated()
            ->assertJsonPath('item.redacted', true);

        $this->assertDatabaseHas('vault_items', [
            'couple_id' => $couple->id,
            'title' => 'First memory',
            'type' => 'note',
        ]);

        $this->assertDatabaseHas('vault_items', [
            'couple_id' => $couple->id,
            'title' => 'Reason list',
            'type' => 'reason',
            'is_sensitive' => 1,
        ]);
    }
}
