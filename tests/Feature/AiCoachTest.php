<?php

namespace Tests\Feature;

use App\Livewire\Coach\Chat as CoachChat;
use App\Models\AiBridgeSuggestion;
use App\Models\AiChat;
use App\Models\Message;
use App\Models\User;
use App\Services\CoupleService;
use App\Services\GeminiService;
use Illuminate\Auth\Access\AuthorizationException;
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

    public function test_bridge_mode_creates_draft_suggestion(): void
    {
        $user = User::factory()->create();
        app(CoupleService::class)->createCouple($user);

        $this->actingAs($user);

        Livewire::test(CoachChat::class)
            ->call('switchMode', 'bridge')
            ->set('newMessage', 'I am upset you were late again.')
            ->call('sendMessage')
            ->call('generateResponse');

        $suggestion = AiBridgeSuggestion::query()->latest()->first();

        $this->assertNotNull($suggestion);
        $this->assertSame(AiBridgeSuggestion::STATUS_DRAFT, $suggestion->status);
        $this->assertDatabaseHas('ai_bridge_suggestions', [
            'id' => $suggestion->id,
            'user_id' => $user->id,
            'status' => AiBridgeSuggestion::STATUS_DRAFT,
        ]);
    }

    public function test_non_owner_cannot_approve_send_or_discard(): void
    {
        $user = User::factory()->create();
        $partner = User::factory()->create();
        $coupleService = app(CoupleService::class);
        $couple = $coupleService->createCouple($user);
        $coupleService->joinCouple($partner, $couple->invite_code);

        $suggestion = app(GeminiService::class)->createDraftSuggestion(
            $couple,
            $user,
            'I feel hurt when plans change suddenly.'
        );

        try {
            app(GeminiService::class)->approveSuggestion($suggestion->id, $partner->id);
            $this->fail('Partner should not approve another user suggestion.');
        } catch (AuthorizationException $e) {
            $this->assertTrue(true);
        }

        try {
            app(GeminiService::class)->discardSuggestion($suggestion->id, $partner->id);
            $this->fail('Partner should not discard another user suggestion.');
        } catch (AuthorizationException $e) {
            $this->assertTrue(true);
        }

        try {
            app(GeminiService::class)->sendApprovedSuggestionToChat($suggestion->id, $partner->id);
            $this->fail('Partner should not send another user suggestion.');
        } catch (AuthorizationException $e) {
            $this->assertTrue(true);
        }
    }

    public function test_approve_transitions_status_and_sets_approved_at(): void
    {
        $user = User::factory()->create();
        $couple = app(CoupleService::class)->createCouple($user);
        $gemini = app(GeminiService::class);

        $suggestion = $gemini->createDraftSuggestion(
            $couple,
            $user,
            'I feel disconnected when we skip check-ins.'
        );

        $approved = $gemini->approveSuggestion($suggestion->id, $user->id);

        $this->assertSame(AiBridgeSuggestion::STATUS_APPROVED, $approved->status);
        $this->assertNotNull($approved->approved_at);
    }

    public function test_send_only_works_if_approved_and_sets_sent_state_and_message(): void
    {
        $user = User::factory()->create();
        $couple = app(CoupleService::class)->createCouple($user);
        $gemini = app(GeminiService::class);

        $suggestion = $gemini->createDraftSuggestion(
            $couple,
            $user,
            'I feel stressed when chores pile up.'
        );

        $this->expectException(\LogicException::class);
        $gemini->sendApprovedSuggestionToChat($suggestion->id, $user->id);
    }

    public function test_sending_approved_suggestion_creates_chat_message_and_sets_sent_at(): void
    {
        $user = User::factory()->create();
        $couple = app(CoupleService::class)->createCouple($user);
        $gemini = app(GeminiService::class);

        $suggestion = $gemini->createDraftSuggestion(
            $couple,
            $user,
            'I feel stressed when chores pile up.'
        );
        $gemini->approveSuggestion($suggestion->id, $user->id);
        $message = $gemini->sendApprovedSuggestionToChat($suggestion->id, $user->id);

        $this->assertSame('text', $message->type);
        $this->assertSame('ai_bridge', $message->metadata['source']);
        $this->assertSame($suggestion->id, $message->metadata['ai_bridge_suggestion_id']);
        $this->assertDatabaseHas('messages', [
            'id' => $message->id,
            'couple_id' => $couple->id,
            'user_id' => $user->id,
        ]);

        $suggestion->refresh();
        $this->assertSame(AiBridgeSuggestion::STATUS_SENT, $suggestion->status);
        $this->assertNotNull($suggestion->sent_at);
    }

    public function test_cannot_send_twice(): void
    {
        $user = User::factory()->create();
        $couple = app(CoupleService::class)->createCouple($user);
        $gemini = app(GeminiService::class);

        $suggestion = $gemini->createDraftSuggestion(
            $couple,
            $user,
            'I feel better when we plan weekends together.'
        );
        $gemini->approveSuggestion($suggestion->id, $user->id);
        $gemini->sendApprovedSuggestionToChat($suggestion->id, $user->id);

        try {
            $gemini->sendApprovedSuggestionToChat($suggestion->id, $user->id);
            $this->fail('Second send should not create another message.');
        } catch (\LogicException $e) {
            $this->assertTrue(true);
        }

        $this->assertSame(1, Message::where('couple_id', $couple->id)->count());
    }

    public function test_partner_cannot_view_other_users_draft_suggestions_in_component(): void
    {
        $user = User::factory()->create();
        $partner = User::factory()->create();
        $coupleService = app(CoupleService::class);
        $couple = $coupleService->createCouple($user);
        $coupleService->joinCouple($partner, $couple->invite_code);

        app(GeminiService::class)->createDraftSuggestion(
            $couple,
            $user,
            'I feel unheard when we interrupt each other.'
        );

        $this->actingAs($partner);
        $component = Livewire::test(CoachChat::class)->call('switchMode', 'bridge');

        $this->assertCount(0, $component->get('bridgeSuggestions'));
    }

    public function test_private_venting_content_is_not_stored_in_source_context(): void
    {
        $user = User::factory()->create();
        app(CoupleService::class)->createCouple($user);
        $rawVenting = 'PRIVATE VENT: do not persist this exact text';

        $this->actingAs($user);
        Livewire::test(CoachChat::class)
            ->call('switchMode', 'bridge')
            ->set('newMessage', $rawVenting)
            ->call('sendMessage')
            ->call('generateResponse');

        $suggestion = AiBridgeSuggestion::query()->latest()->firstOrFail();
        $storedContext = json_encode($suggestion->source_context ?? []);

        $this->assertStringNotContainsString($rawVenting, $storedContext);
    }
}
