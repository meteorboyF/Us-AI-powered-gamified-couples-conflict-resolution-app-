<?php

namespace App\Http\Controllers;

use App\Domain\AI\Prompts\PromptBuilder;
use App\Domain\AI\Safety\SafetyClassifier;
use App\Events\AiCoach\AiDraftCreated;
use App\Events\AiCoach\AiMessageCreated;
use App\Events\AiCoach\AiSessionClosed;
use App\Models\AiDraft;
use App\Models\AiMessage;
use App\Models\AiSession;
use App\Models\Couple;
use App\Services\AI\Contracts\AiProvider;
use App\Services\AI\Exceptions\AiProviderException;
use App\Support\CoupleContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiCoachController extends Controller
{
    public function index(Request $request, CoupleContext $context): JsonResponse
    {
        $this->ensureFeatureEnabled();
        $couple = $this->resolveCouple($request, $context);
        $this->authorize('viewAny', [AiSession::class, $couple->id]);

        $sessions = AiSession::query()
            ->where('couple_id', $couple->id)
            ->orderByDesc('id')
            ->get()
            ->map(fn (AiSession $session) => [
                'id' => $session->id,
                'mode' => $session->mode,
                'title' => $session->title,
                'status' => $session->status,
                'safety_flags' => $session->safety_flags ?? [],
                'created_at' => $session->created_at?->toIso8601String(),
            ])
            ->values();

        return response()->json(['sessions' => $sessions]);
    }

    public function store(Request $request, CoupleContext $context): JsonResponse
    {
        $this->ensureFeatureEnabled();

        $validated = $request->validate([
            'mode' => ['required', 'string', 'in:vent,bridge,repair'],
            'title' => ['nullable', 'string', 'max:255'],
        ]);

        $couple = $this->resolveCouple($request, $context);
        $this->authorize('create', [AiSession::class, $couple->id]);

        $session = AiSession::query()->create([
            'couple_id' => $couple->id,
            'created_by_user_id' => $request->user()->id,
            'mode' => $validated['mode'],
            'title' => $validated['title'] ?? null,
            'status' => 'active',
            'safety_flags' => [],
            'meta' => [],
        ]);

        return response()->json([
            'id' => $session->id,
            'mode' => $session->mode,
            'status' => $session->status,
        ], 201);
    }

    public function show(Request $request, CoupleContext $context, AiSession $session): JsonResponse
    {
        $this->ensureFeatureEnabled();
        $couple = $this->resolveCouple($request, $context);
        $this->ensureSessionInCouple($session, $couple);
        $this->authorize('view', $session);

        $messages = $session->messages()
            ->orderBy('id')
            ->get()
            ->map(fn (AiMessage $message) => [
                'id' => $message->id,
                'sender_type' => $message->sender_type,
                'sender_user_id' => $message->sender_user_id,
                'content' => $message->content,
                'role' => $message->role,
                'tokens_in' => $message->tokens_in,
                'tokens_out' => $message->tokens_out,
                'safety' => $message->safety ?? [],
                'created_at' => $message->created_at?->toIso8601String(),
            ])->values();

        $drafts = $session->drafts()
            ->orderByDesc('id')
            ->get()
            ->map(fn (AiDraft $draft) => [
                'id' => $draft->id,
                'draft_type' => $draft->draft_type,
                'title' => $draft->title,
                'content' => $draft->content,
                'status' => $draft->status,
                'accepted_at' => $draft->accepted_at?->toIso8601String(),
            ])->values();

        return response()->json([
            'session' => [
                'id' => $session->id,
                'mode' => $session->mode,
                'title' => $session->title,
                'status' => $session->status,
                'safety_flags' => $session->safety_flags ?? [],
            ],
            'messages' => $messages,
            'drafts' => $drafts,
        ]);
    }

    public function message(
        Request $request,
        CoupleContext $context,
        AiSession $session,
        SafetyClassifier $safetyClassifier,
        PromptBuilder $promptBuilder,
        AiProvider $provider,
    ): JsonResponse {
        $this->ensureFeatureEnabled();

        $validated = $request->validate([
            'content' => ['required', 'string', 'max:'.(int) config('us.ai.max_input_chars', 4000)],
        ]);

        $couple = $this->resolveCouple($request, $context);
        $this->ensureSessionInCouple($session, $couple);
        $this->authorize('update', $session);

        $userMessage = AiMessage::query()->create([
            'ai_session_id' => $session->id,
            'sender_type' => 'user',
            'sender_user_id' => $request->user()->id,
            'content' => $validated['content'],
            'role' => 'user',
            'safety' => [],
        ]);
        event(new AiMessageCreated(
            (int) $couple->id,
            (int) $session->id,
            [
                'id' => $userMessage->id,
                'sender_type' => $userMessage->sender_type,
                'sender_user_id' => $userMessage->sender_user_id,
                'content' => $userMessage->content,
                'created_at' => $userMessage->created_at?->toIso8601String(),
            ],
        ));

        $classification = $safetyClassifier->classify($validated['content']);
        $flags = $classification['flags'];

        $session->forceFill([
            'safety_flags' => $flags,
        ])->save();

        $messages = $promptBuilder->build($session->mode, $validated['content'], $flags);
        $messages[0]['content'] = $classification['system_prompt'];

        try {
            $providerResponse = $provider->chat($messages, [
                'mode' => $session->mode,
                'session_id' => $session->id,
            ]);
            $assistantText = $this->sanitizeAssistantText($providerResponse->text);
            $providerSafety = $providerResponse->safety;
            $tokensIn = $providerResponse->tokensIn;
            $tokensOut = $providerResponse->tokensOut;
        } catch (AiProviderException) {
            $assistantText = 'I need a pause before responding. Please take a breath and try again in a moment.';
            $providerSafety = ['provider_error' => true];
            $tokensIn = null;
            $tokensOut = null;
        }

        $assistantMessage = AiMessage::query()->create([
            'ai_session_id' => $session->id,
            'sender_type' => 'ai',
            'sender_user_id' => null,
            'content' => $assistantText,
            'role' => 'assistant',
            'tokens_in' => $tokensIn,
            'tokens_out' => $tokensOut,
            'safety' => $providerSafety,
        ]);
        event(new AiMessageCreated(
            (int) $couple->id,
            (int) $session->id,
            [
                'id' => $assistantMessage->id,
                'sender_type' => $assistantMessage->sender_type,
                'sender_user_id' => $assistantMessage->sender_user_id,
                'content' => $assistantMessage->content,
                'created_at' => $assistantMessage->created_at?->toIso8601String(),
            ],
        ));

        $draft = null;
        if (in_array($session->mode, ['bridge', 'repair'], true)) {
            $draft = AiDraft::query()->create([
                'ai_session_id' => $session->id,
                'created_by_user_id' => $request->user()->id,
                'draft_type' => $session->mode === 'bridge' ? 'bridge_message' : 'repair_plan',
                'title' => $session->mode === 'bridge' ? 'Bridge Draft' : 'Repair Plan Draft',
                'content' => $assistantText,
                'status' => 'draft',
            ]);
            event(new AiDraftCreated(
                (int) $couple->id,
                (int) $session->id,
                [
                    'id' => $draft->id,
                    'draft_type' => $draft->draft_type,
                    'title' => $draft->title,
                    'content' => $draft->content,
                    'status' => $draft->status,
                    'created_at' => $draft->created_at?->toIso8601String(),
                ],
            ));
        }

        return response()->json([
            'user_message_id' => $userMessage->id,
            'ai_message_id' => $assistantMessage->id,
            'ai_text' => $assistantText,
            'safety_flags' => $flags,
            'draft' => $draft ? [
                'id' => $draft->id,
                'draft_type' => $draft->draft_type,
                'status' => $draft->status,
            ] : null,
        ]);
    }

    public function close(Request $request, CoupleContext $context, AiSession $session): JsonResponse
    {
        $this->ensureFeatureEnabled();
        $couple = $this->resolveCouple($request, $context);
        $this->ensureSessionInCouple($session, $couple);
        $this->authorize('update', $session);

        $session->forceFill(['status' => 'closed'])->save();
        event(new AiSessionClosed(
            (int) $couple->id,
            (int) $session->id,
            (string) $session->status,
            $session->updated_at?->toIso8601String(),
        ));

        return response()->json(['closed' => true]);
    }

    public function acceptDraft(Request $request, CoupleContext $context, AiSession $session, AiDraft $draft): JsonResponse
    {
        $this->ensureFeatureEnabled();
        $couple = $this->resolveCouple($request, $context);
        $this->ensureSessionInCouple($session, $couple);
        $this->ensureDraftInSession($draft, $session);
        $this->authorize('update', $draft);

        $draft->forceFill([
            'status' => 'accepted',
            'accepted_at' => now(),
        ])->save();

        return response()->json(['accepted' => true]);
    }

    public function discardDraft(Request $request, CoupleContext $context, AiSession $session, AiDraft $draft): JsonResponse
    {
        $this->ensureFeatureEnabled();
        $couple = $this->resolveCouple($request, $context);
        $this->ensureSessionInCouple($session, $couple);
        $this->ensureDraftInSession($draft, $session);
        $this->authorize('update', $draft);

        $draft->forceFill([
            'status' => 'discarded',
            'accepted_at' => null,
        ])->save();

        return response()->json(['discarded' => true]);
    }

    private function ensureFeatureEnabled(): void
    {
        if (! config('us.features.ai_coach_v1', true)) {
            abort(404, 'Feature not available.');
        }
    }

    private function resolveCouple(Request $request, CoupleContext $context): Couple
    {
        $user = $request->user();

        if ($user->current_couple_id && ! $user->couples()->whereKey($user->current_couple_id)->exists()) {
            abort(403, 'Current couple is not accessible.');
        }

        $couple = $context->resolve();

        if (! $couple) {
            abort(409, 'No couple selected');
        }

        return $couple;
    }

    private function ensureSessionInCouple(AiSession $session, Couple $couple): void
    {
        if ((int) $session->couple_id !== (int) $couple->id) {
            abort(404, 'Session not found.');
        }
    }

    private function ensureDraftInSession(AiDraft $draft, AiSession $session): void
    {
        if ((int) $draft->ai_session_id !== (int) $session->id) {
            abort(404, 'Draft not found.');
        }
    }

    private function sanitizeAssistantText(string $text): string
    {
        $disallowed = ['kill yourself', 'harm yourself', 'legal advice', 'medical advice'];
        $lower = mb_strtolower($text);

        foreach ($disallowed as $phrase) {
            if (str_contains($lower, $phrase)) {
                return 'I can help with communication coaching. Please pause and seek qualified local support for safety, legal, or medical concerns.';
            }
        }

        return $text;
    }
}
