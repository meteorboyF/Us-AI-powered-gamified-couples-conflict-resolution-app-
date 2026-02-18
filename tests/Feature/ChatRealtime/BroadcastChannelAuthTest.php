<?php

namespace Tests\Feature\ChatRealtime;

use App\Broadcasting\CoupleChannelAuthorizer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\ChatV1\Concerns\CreatesChatV1Context;
use Tests\TestCase;

class BroadcastChannelAuthTest extends TestCase
{
    use CreatesChatV1Context;
    use RefreshDatabase;

    public function test_member_authorizer_returns_true_for_couple_member(): void
    {
        $ctx = $this->createCouplePair();
        $authorizer = app(CoupleChannelAuthorizer::class);

        $this->assertTrue($authorizer($ctx['user'], $ctx['couple']->id));
    }

    public function test_member_authorizer_returns_false_for_non_member(): void
    {
        $ctx = $this->createCouplePair();
        $intruder = User::factory()->create();
        $authorizer = app(CoupleChannelAuthorizer::class);

        $this->assertFalse($authorizer($intruder, $ctx['couple']->id));
    }
}
