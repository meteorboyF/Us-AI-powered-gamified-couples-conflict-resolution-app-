<?php

namespace Tests\Feature;

use App\Models\Memory;
use App\Models\User;
use App\Services\ChatService;
use App\Services\CoupleService;
use App\Services\VaultService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_message_send_persists_and_can_be_read_without_duplication(): void
    {
        $sender = User::factory()->create();
        $partner = User::factory()->create();
        $coupleService = app(CoupleService::class);
        $couple = $coupleService->createCouple($sender);
        $coupleService->joinCouple($partner, $couple->invite_code);

        $chatService = app(ChatService::class);
        $chatService->sendMessage($couple, $sender, 'Hello there');

        $firstRead = $chatService->getMessages($couple, $sender);
        $secondRead = $chatService->getMessages($couple, $sender);

        $this->assertDatabaseHas('messages', [
            'couple_id' => $couple->id,
            'user_id' => $sender->id,
            'content' => 'Hello there',
            'type' => 'text',
        ]);
        $this->assertCount(1, $firstRead);
        $this->assertCount(1, $secondRead);
        $this->assertSame($firstRead->first()->id, $secondRead->first()->id);
    }

    public function test_reactions_attach_for_member_and_cannot_be_forged_by_outsider(): void
    {
        $user = User::factory()->create();
        $partner = User::factory()->create();
        $outsider = User::factory()->create();
        $outsiderPartner = User::factory()->create();

        $coupleService = app(CoupleService::class);
        $couple = $coupleService->createCouple($user);
        $coupleService->joinCouple($partner, $couple->invite_code);

        $outsiderCouple = $coupleService->createCouple($outsider);
        $coupleService->joinCouple($outsiderPartner, $outsiderCouple->invite_code);

        $memory = Memory::create([
            'couple_id' => $couple->id,
            'created_by' => $user->id,
            'type' => 'text',
            'description' => 'Memory text',
            'visibility' => 'shared',
        ]);

        $vaultService = app(VaultService::class);
        $reaction = $vaultService->addReaction($memory, $partner, 'heart');
        $this->assertSame('heart', $reaction->reaction);

        $this->expectException(AuthorizationException::class);
        $vaultService->addReaction($memory, $outsider, 'heart');
    }

    public function test_message_order_is_stable_across_repeated_fetches(): void
    {
        $sender = User::factory()->create();
        $partner = User::factory()->create();
        $coupleService = app(CoupleService::class);
        $couple = $coupleService->createCouple($sender);
        $coupleService->joinCouple($partner, $couple->invite_code);

        $chatService = app(ChatService::class);
        $chatService->sendMessage($couple, $sender, 'msg-1');
        $chatService->sendMessage($couple, $partner, 'msg-2');
        $chatService->sendMessage($couple, $sender, 'msg-3');

        $firstReadIds = $chatService->getMessages($couple, $sender)->pluck('id')->values()->all();
        $secondReadIds = $chatService->getMessages($couple, $sender)->pluck('id')->values()->all();

        $this->assertSame($firstReadIds, $secondReadIds);
        $this->assertSame(['msg-1', 'msg-2', 'msg-3'], $chatService->getMessages($couple, $sender)->pluck('content')->all());
    }
}
