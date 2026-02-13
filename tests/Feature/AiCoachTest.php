<?php

namespace Tests\Feature;

use App\Livewire\Coach\Chat as CoachChat;
use App\Models\AiChat;
use App\Models\User;
use App\Services\CoupleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AiCoachTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_gets_private_ai_chat_session_scoped_to_them(): void
    {
        $user = User::factory()->create();
        $partner = User::factory()->create();
        $coupleService = app(CoupleService::class);
        $couple = $coupleService->createCouple($user);
        $coupleService->joinCouple($partner, $couple->invite_code);

        $this->actingAs($user);
        Livewire::test(CoachChat::class)->assertSet('mode', 'vent');

        $this->actingAs($partner);
        Livewire::test(CoachChat::class)->assertSet('mode', 'vent');

        $this->assertSame(2, AiChat::where('couple_id', $couple->id)->where('is_active', true)->count());
        $this->assertSame(1, AiChat::where('couple_id', $couple->id)->where('user_id', $user->id)->count());
        $this->assertSame(1, AiChat::where('couple_id', $couple->id)->where('user_id', $partner->id)->count());
    }

    public function test_switching_mode_archives_old_chat_and_creates_new_one_for_same_user(): void
    {
        $user = User::factory()->create();
        $couple = app(CoupleService::class)->createCouple($user);

        $this->actingAs($user);
        Livewire::test(CoachChat::class)
            ->call('switchMode', 'bridge')
            ->assertSet('mode', 'bridge');

        $this->assertSame(2, AiChat::where('user_id', $user->id)->where('couple_id', $couple->id)->count());
        $this->assertSame(1, AiChat::where('user_id', $user->id)->where('couple_id', $couple->id)->where('is_active', true)->count());
        $this->assertSame('bridge', AiChat::where('user_id', $user->id)->where('is_active', true)->value('type'));
    }
}

