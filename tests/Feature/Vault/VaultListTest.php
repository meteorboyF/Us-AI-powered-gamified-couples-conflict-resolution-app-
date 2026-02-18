<?php

namespace Tests\Feature\Vault;

use App\Models\User;
use App\Models\VaultItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Vault\Concerns\CreatesVaultContext;
use Tests\TestCase;

class VaultListTest extends TestCase
{
    use CreatesVaultContext;
    use RefreshDatabase;

    public function test_member_can_list_items_and_sensitive_item_is_redacted(): void
    {
        $user = User::factory()->create();
        [$couple] = $this->createCoupleWithPartner($user);

        VaultItem::query()->create([
            'couple_id' => $couple->id,
            'created_by_user_id' => $user->id,
            'type' => 'note',
            'title' => 'Normal Note',
            'body' => 'Visible content',
            'is_sensitive' => false,
            'meta' => ['consent_required' => false],
        ]);

        VaultItem::query()->create([
            'couple_id' => $couple->id,
            'created_by_user_id' => $user->id,
            'type' => 'reason',
            'title' => 'Sensitive Item',
            'body' => 'Should not appear',
            'is_sensitive' => true,
            'meta' => ['consent_required' => true],
        ]);

        $response = $this->actingAs($user)->getJson('/vault')->assertOk();

        $response->assertJsonFragment(['title' => 'Normal Note', 'redacted' => false]);
        $response->assertJsonFragment(['title' => 'Sensitive Item', 'redacted' => true]);
    }
}
