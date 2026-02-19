<?php

namespace Tests\Feature\UI\Vault;

use App\Models\User;
use App\Models\VaultItem;
use App\Models\VaultUnlockRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Vault\Concerns\CreatesVaultContext;
use Tests\TestCase;

class VaultUiActionsTest extends TestCase
{
    use CreatesVaultContext;
    use RefreshDatabase;

    public function test_create_route_creates_vault_item_and_redirects(): void
    {
        $user = User::factory()->create();
        [$couple] = $this->createCoupleWithPartner($user);

        $this->actingAs($user)
            ->post('/vault-ui/create', [
                'type' => 'note',
                'title' => 'UI created item',
                'body' => 'Stored through UI route.',
            ])
            ->assertRedirect('/vault-ui');

        $this->assertDatabaseHas('vault_items', [
            'couple_id' => $couple->id,
            'created_by_user_id' => $user->id,
            'title' => 'UI created item',
            'type' => 'note',
        ]);
    }

    public function test_unlock_request_and_approval_permissions(): void
    {
        $owner = User::factory()->create();
        [$couple, $partner] = $this->createCoupleWithPartner($owner);

        $item = VaultItem::query()->create([
            'couple_id' => $couple->id,
            'created_by_user_id' => $owner->id,
            'type' => 'reason',
            'title' => 'Sensitive UI item',
            'body' => 'Private',
            'is_sensitive' => true,
            'meta' => ['consent_required' => true],
        ]);

        $this->actingAs($owner)
            ->post('/vault-ui/'.$item->id.'/unlock-request')
            ->assertRedirect('/vault-ui');

        $unlockRequest = VaultUnlockRequest::query()->where('vault_item_id', $item->id)->firstOrFail();

        $this->actingAs($owner)
            ->post('/vault-ui/unlock/'.$unlockRequest->id.'/approve')
            ->assertForbidden();

        $this->actingAs($partner)
            ->post('/vault-ui/unlock/'.$unlockRequest->id.'/approve')
            ->assertRedirect('/vault-ui');

        $this->assertDatabaseHas('vault_unlock_requests', [
            'id' => $unlockRequest->id,
            'status' => 'approved',
        ]);
    }
}
