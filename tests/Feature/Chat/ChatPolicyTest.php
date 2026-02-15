<?php

namespace Tests\Feature\Chat;

use App\Domain\Couples\CoupleContext;
use App\Models\Chat;
use App\Models\ChatParticipant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class ChatPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_participant_in_current_couple_can_view_chat(): void
    {
        $user = User::factory()->create();
        $chat = Chat::factory()->create(['couple_id' => 77]);

        ChatParticipant::query()->create([
            'chat_id' => $chat->id,
            'user_id' => $user->id,
            'joined_at' => now(),
        ]);

        app()->instance(CoupleContext::class, new class(77) extends CoupleContext
        {
            public function __construct(private readonly ?int $coupleId) {}

            public function currentCoupleId(): ?int
            {
                return $this->coupleId;
            }
        });

        $this->assertTrue(Gate::forUser($user)->allows('view', $chat));
    }

    public function test_participant_cannot_view_chat_outside_current_couple(): void
    {
        $user = User::factory()->create();
        $chat = Chat::factory()->create(['couple_id' => 77]);

        ChatParticipant::query()->create([
            'chat_id' => $chat->id,
            'user_id' => $user->id,
            'joined_at' => now(),
        ]);

        app()->instance(CoupleContext::class, new class(88) extends CoupleContext
        {
            public function __construct(private readonly ?int $coupleId) {}

            public function currentCoupleId(): ?int
            {
                return $this->coupleId;
            }
        });

        $this->assertTrue(Gate::forUser($user)->denies('view', $chat));
    }
}
