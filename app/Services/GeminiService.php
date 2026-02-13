<?php

namespace App\Services;

use App\Models\AiBridgeSuggestion;
use App\Models\Couple;
use App\Models\Message;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected $apiKey;

    protected $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key');
    }

    /**
     * Send a request to the Gemini API.
     */
    public function chat(array $messages, string $mode = 'vent'): string
    {
        // Add system instruction based on mode
        $systemInstruction = $this->getSystemPrompt($mode);

        // Transform messages for Gemini format
        $contents = [];

        // Add system prompt as the first user message (Gemini best practice for simple chat)
        // or use the system_instruction field if supported by the library.
        // For REST API, we'll prepend it to the context.
        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => 'System Instruction: '.$systemInstruction]],
        ];

        $contents[] = [
            'role' => 'model',
            'parts' => [['text' => 'Understood. I will act as the localized relationship coach based on these instructions.']],
        ];

        foreach ($messages as $msg) {
            $role = $msg['role'] === 'user' ? 'user' : 'model';
            $contents[] = [
                'role' => $role,
                'parts' => [['text' => $msg['content']]],
            ];
        }

        if (empty($this->apiKey)) {
            return $this->mockResponse($mode);
        }

        try {
            $response = Http::post($this->baseUrl.'?key='.$this->apiKey, [
                'contents' => $contents,
                'generationConfig' => [
                    'temperature' => 0.7,
                    'maxOutputTokens' => 500,
                ],
            ]);

            if ($response->successful()) {
                return $response->json('candidates.0.content.parts.0.text') ?? "I'm deep in thought but couldn't formulate a response.";
            } else {
                Log::error('Gemini API Error: '.$response->body());

                return "I'm having trouble connecting to my brain right now. Please try again later. (Error: ".$response->status().')';
            }
        } catch (\Exception $e) {
            Log::error('Gemini Connection Exception: '.$e->getMessage());

            return 'Connection error. Please check your internet connection and try again.';
        }
    }

    /**
     * Get the system prompt customization based on the mode.
     */
    protected function getSystemPrompt(string $mode): string
    {
        if ($mode === 'vent') {
            return 'You are an empathetic, non-judgmental relationship coach. 
            Your goal is to provide a safe space for the user to express their frustrations. 
            Validate their feelings using techniques like reflective listening. 
            Do NOT try to solve the problem immediately. 
            Do NOT take sides. 
            Ask 1-2 open-ended clarifying questions to help them explore their emotions deeper. 
            Keep responses concise (max 3 sentences) and warm.';
        }

        // Bridge mode = helping reformulate
        return "You are a communication expert specializing in conflict resolution. 
        Your goal is to help the user translate their complaints or frustrations into constructive 'I' statements. 
        Guide them to express: 'I feel [emotion] when [situation] because [impact/need], and I would really appreciate [request]'. 
        Remove blame, criticism, and 'you' statements. 
        If the user is very angry, first help them calm down before suggesting phrasing.
        Keep responses concise and actionable.";
    }

    /**
     * Mock response for testing without API key.
     */
    protected function mockResponse(string $mode): string
    {
        if ($mode === 'vent') {
            return 'I hear you. It sounds really tough to deal with that. (This is a MOCK response because no API key was found. Please add GEMINI_API_KEY to .env)';
        }

        return "Try saying: 'I feel overwhelmed when tasks stack up...' (This is a MOCK response because no API key was found).";
    }

    public function createDraftSuggestion(
        Couple $couple,
        User $user,
        string $suggestedMessage,
        ?array $sourceContext = null
    ): AiBridgeSuggestion {
        $this->assertCoupleMember($couple, $user);

        return AiBridgeSuggestion::create([
            'couple_id' => $couple->id,
            'user_id' => $user->id,
            'source_context' => $this->sanitizeSourceContext($sourceContext),
            'suggested_message' => trim($suggestedMessage),
            'status' => AiBridgeSuggestion::STATUS_DRAFT,
        ]);
    }

    public function approveSuggestion(int $suggestionId, int $userId): AiBridgeSuggestion
    {
        $suggestion = AiBridgeSuggestion::findOrFail($suggestionId);
        $this->assertOwner($suggestion, $userId);
        $this->assertCoupleMember($suggestion->couple, $suggestion->user);

        if ($suggestion->status === AiBridgeSuggestion::STATUS_SENT) {
            throw new \LogicException('Sent suggestions cannot be modified.');
        }

        if ($suggestion->status === AiBridgeSuggestion::STATUS_DISCARDED) {
            throw new \LogicException('Discarded suggestions cannot be approved.');
        }

        if ($suggestion->status !== AiBridgeSuggestion::STATUS_APPROVED) {
            $suggestion->update([
                'status' => AiBridgeSuggestion::STATUS_APPROVED,
                'approved_at' => now(),
            ]);
        }

        return $suggestion->fresh();
    }

    public function discardSuggestion(int $suggestionId, int $userId): AiBridgeSuggestion
    {
        $suggestion = AiBridgeSuggestion::findOrFail($suggestionId);
        $this->assertOwner($suggestion, $userId);
        $this->assertCoupleMember($suggestion->couple, $suggestion->user);

        if ($suggestion->status === AiBridgeSuggestion::STATUS_SENT) {
            throw new \LogicException('Sent suggestions cannot be discarded.');
        }

        if ($suggestion->status !== AiBridgeSuggestion::STATUS_DISCARDED) {
            $suggestion->update([
                'status' => AiBridgeSuggestion::STATUS_DISCARDED,
            ]);
        }

        return $suggestion->fresh();
    }

    public function sendApprovedSuggestionToChat(int $suggestionId, int $userId): Message
    {
        return DB::transaction(function () use ($suggestionId, $userId) {
            $suggestion = AiBridgeSuggestion::query()->lockForUpdate()->findOrFail($suggestionId);
            $this->assertOwner($suggestion, $userId);
            $this->assertCoupleMember($suggestion->couple, $suggestion->user);

            if ($suggestion->status === AiBridgeSuggestion::STATUS_SENT) {
                throw new \LogicException('Suggestion already sent.');
            }

            if ($suggestion->status !== AiBridgeSuggestion::STATUS_APPROVED) {
                throw new \LogicException('Only approved suggestions can be sent.');
            }

            $message = Message::create([
                'couple_id' => $suggestion->couple_id,
                'user_id' => $suggestion->user_id,
                'content' => $suggestion->suggested_message,
                'type' => 'text',
                'metadata' => [
                    'source' => 'ai_bridge',
                    'ai_bridge_suggestion_id' => $suggestion->id,
                ],
            ]);

            $suggestion->update([
                'status' => AiBridgeSuggestion::STATUS_SENT,
                'sent_at' => now(),
            ]);

            return $message;
        });
    }

    protected function sanitizeSourceContext(?array $sourceContext): ?array
    {
        if ($sourceContext === null) {
            return null;
        }

        $allowed = Arr::only($sourceContext, ['mode', 'ai_chat_id']);

        return empty($allowed) ? null : $allowed;
    }

    protected function assertOwner(AiBridgeSuggestion $suggestion, int $userId): void
    {
        if ($suggestion->user_id !== $userId) {
            throw new AuthorizationException('Unauthorized bridge suggestion access.');
        }
    }

    protected function assertCoupleMember(Couple $couple, User $user): void
    {
        if (! $couple->isActive()) {
            throw new AuthorizationException('Unauthorized couple access.');
        }

        $isMember = $couple->users()
            ->where('users.id', $user->id)
            ->where('couple_user.is_active', true)
            ->exists();

        if (! $isMember) {
            throw new AuthorizationException('Unauthorized couple access.');
        }
    }
}
