<?php

namespace Tests\Feature\Vault;

use App\Models\User;
use App\Models\VaultItem;
use App\Models\VaultUnlockRequest;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Vault\Concerns\CreatesVaultContext;
use Tests\TestCase;

class VaultConsentFlowTest extends TestCase
{
    use CreatesVaultContext;
    use RefreshDatabase;

    public function test_unlock_request_and_partner_approval_reveals_sensitive_item_within_window(): void
    {
        Carbon::setTestNow('2026-02-18 12:00:00');

        $requester = User::factory()->create();
        [$couple, $partner] = $this->createCoupleWithPartner($requester);

        $item = VaultItem::query()->create([
            'couple_id' => $couple->id,
            'created_by_user_id' => $requester->id,
            'type' => 'reason',
            'title' => 'Sensitive reason',
            'body' => 'Private supportive detail',
            'is_sensitive' => true,
            'is_locked' => false,
            'meta' => ['consent_required' => true],
        ]);

        $requestResponse = $this->actingAs($requester)
            ->postJson('/vault/'.$item->id.'/unlock-request')
            ->assertCreated()
            ->assertJsonPath('status', 'pending');

        $unlockRequestId = (int) $requestResponse->json('unlock_request_id');

        $this->actingAs($partner)
            ->postJson('/vault/unlock/'.$unlockRequestId.'/approve')
            ->assertOk()
            ->assertJsonPath('status', 'approved');

        $this->actingAs($requester)
            ->getJson('/vault/'.$item->id)
            ->assertOk()
            ->assertJsonPath('item.redacted', false)
            ->assertJsonPath('item.body', 'Private supportive detail');
    }

    public function test_expired_approved_unlock_still_keeps_item_redacted(): void
    {
        Carbon::setTestNow('2026-02-18 12:00:00');

        $user = User::factory()->create();
        [$couple] = $this->createCoupleWithPartner($user);

        $item = VaultItem::query()->create([
            'couple_id' => $couple->id,
            'created_by_user_id' => $user->id,
            'type' => 'note',
            'title' => 'Expired lock',
            'body' => 'Should stay hidden',
            'is_sensitive' => true,
            'meta' => ['consent_required' => true],
        ]);

        VaultUnlockRequest::query()->create([
            'vault_item_id' => $item->id,
            'requested_by_user_id' => $user->id,
            'status' => 'approved',
            'expires_at' => Carbon::now()->subMinute(),
        ]);

        $this->actingAs($user)
            ->getJson('/vault/'.$item->id)
            ->assertOk()
            ->assertJsonPath('item.redacted', true)
            ->assertJsonPath('item.body', null);
    }
}
