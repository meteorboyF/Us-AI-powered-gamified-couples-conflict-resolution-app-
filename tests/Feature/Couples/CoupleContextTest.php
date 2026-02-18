<?php

namespace Tests\Feature\Couples;

use App\Models\Couple;
use App\Models\CoupleMember;
use App\Models\User;
use App\Support\CoupleContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoupleContextTest extends TestCase
{
    use RefreshDatabase;

    public function test_context_sets_current_couple_when_user_belongs_to_exactly_one_couple(): void
    {
        $user = User::factory()->create();
        $couple = Couple::query()->create([
            'name' => 'Test Couple',
            'invite_code' => 'INVITE01',
            'created_by_user_id' => $user->id,
        ]);

        CoupleMember::query()->create([
            'couple_id' => $couple->id,
            'user_id' => $user->id,
            'role' => 'owner',
            'joined_at' => now(),
        ]);

        $this->actingAs($user);

        $resolved = app(CoupleContext::class)->resolve();

        $this->assertNotNull($resolved);
        $this->assertSame($couple->id, $resolved->id);
        $this->assertSame($couple->id, $user->fresh()->current_couple_id);
    }
}
