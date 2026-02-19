<?php

namespace Tests\Feature\UI\Vault;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Vault\Concerns\CreatesVaultContext;
use Tests\TestCase;

class VaultUiPageTest extends TestCase
{
    use CreatesVaultContext;
    use RefreshDatabase;

    public function test_user_with_current_couple_can_view_vault_page(): void
    {
        $user = User::factory()->create();
        $this->createCoupleWithPartner($user);

        $this->actingAs($user)
            ->get('/vault-ui')
            ->assertOk()
            ->assertSee('Vault');
    }

    public function test_user_without_current_couple_sees_no_couple_message(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/vault-ui')
            ->assertOk()
            ->assertSee('No couple selected')
            ->assertSee('/couple');
    }
}
