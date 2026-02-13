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
use Throwable;

class GeminiService
{
    protected $apiKey;

    protected string $baseUrl;

    protected string $model;

    protected int $timeoutSeconds = 10;

    protected int $maxRetries = 2;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key');
        $this->baseUrl = rtrim(config('services.gemini.endpoint', 'https://generativelanguage.googleapis.com/v1beta'), '/');
        $this->model = config('services.gemini.model', 'gemini-1.5-flash');
    }

    /**
     * Send a request to the Gemini API.
     */
    public function chat(array $messages, string $mode = 'vent'): string
    {
        return $this->coachReply($messages, $mode)['text'];
    }

    /**
     * Coach chat with retries and safe deterministic fallback.
     *
     * @return array{text: string, source: 'gemini'|'fallback', used_fallback: bool, notice: ?string}
     */
    public function coachReply(array $messages, string $mode = 'vent'): array
    {
        return $this->generateWithSystemInstruction($messages, $this->getSystemPrompt($mode), $mode);
    }

    /**
     * Generic Gemini call for text output using a custom system instruction.
     *
     * @return array{text: string, source: 'gemini'|'fallback', used_fallback: bool, notice: ?string}
     */
    public function generateWithSystemInstruction(array $messages, string $systemInstruction, string $fallbackMode = 'vent'): array
    {
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
            return [
                'text' => $this->fallbackCoachResponse($messages, $fallbackMode),
                'source' => 'fallback',
                'used_fallback' => true,
                'notice' => 'Coach is currently using built-in support mode.',
            ];
        }

        $maxAttempts = $this->maxRetries + 1;
        $url = "{$this->baseUrl}/models/{$this->model}:generateContent?key={$this->apiKey}";

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                $response = Http::timeout($this->timeoutSeconds)->post($url, [
                    'contents' => $contents,
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'maxOutputTokens' => 500,
                    ],
                ]);

                if (! $response->successful()) {
                    throw new \RuntimeException('gemini_http_'.$response->status());
                }

                $assistantText = $this->extractAssistantText($response->json());

                if ($assistantText === null) {
                    throw new \UnexpectedValueException('gemini_invalid_response_shape');
                }

                return [
                    'text' => $assistantText,
                    'source' => 'gemini',
                    'used_fallback' => false,
                    'notice' => null,
                ];
            } catch (Throwable $e) {
                Log::warning('Gemini chat attempt failed', [
                    'attempt' => $attempt,
                    'max_attempts' => $maxAttempts,
                    'mode' => $fallbackMode,
                    'error_class' => get_class($e),
                    'error_code' => $e->getCode(),
                    'error_message' => $e->getMessage(),
                ]);

                if ($attempt < $maxAttempts) {
                    $this->shortBackoff($attempt);

                    continue;
                }
            }
        }

        return [
            'text' => $this->fallbackCoachResponse($messages, $fallbackMode),
            'source' => 'fallback',
            'used_fallback' => true,
            'notice' => 'Coach had trouble reaching AI and switched to built-in support mode.',
        ];
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

    protected function extractAssistantText($payload): ?string
    {
        if (! is_array($payload)) {
            return null;
        }

        $parts = data_get($payload, 'candidates.0.content.parts');

        if (! is_array($parts)) {
            return null;
        }

        $text = collect($parts)
            ->map(fn ($part) => is_array($part) ? trim((string) ($part['text'] ?? '')) : '')
            ->filter()
            ->implode("\n");

        return $text === '' ? null : $text;
    }

    protected function shortBackoff(int $attempt): void
    {
        // 150ms, 300ms for retries 1 and 2.
        $delayMs = 150 * (2 ** ($attempt - 1));
        usleep($delayMs * 1000);
    }

    protected function fallbackCoachResponse(array $messages, string $mode): string
    {
        $latestUserMessage = $this->latestUserMessage($messages);
        $emotion = $this->inferEmotion($latestUserMessage);
        $need = $this->inferNeed($latestUserMessage);

        if ($mode === 'bridge') {
            return "I hear how important this is to you, and you deserve to express it clearly.\n".
                "What specific moment felt hardest for you?\n".
                "Try: \"I feel {$emotion} when [specific situation] because I need {$need}. I would appreciate [clear request].\"\n".
                'Repair step: start with one appreciation, then share one specific request.';
        }

        return "It makes sense that you are feeling {$emotion} right now.\n".
            'What feels most important for your partner to understand first?\n'.
            "If helpful, try: \"I feel {$emotion} when [situation] because I need {$need}.\"\n".
            'Repair step: take a 10-minute reset, then return with one calm request.';
    }

    protected function latestUserMessage(array $messages): string
    {
        for ($i = count($messages) - 1; $i >= 0; $i--) {
            if (($messages[$i]['role'] ?? null) === 'user') {
                return (string) ($messages[$i]['content'] ?? '');
            }
        }

        return '';
    }

    protected function inferEmotion(string $message): string
    {
        $lower = mb_strtolower($message);

        if (str_contains($lower, 'angry') || str_contains($lower, 'mad') || str_contains($lower, 'furious')) {
            return 'angry';
        }
        if (str_contains($lower, 'sad') || str_contains($lower, 'hurt') || str_contains($lower, 'cry')) {
            return 'hurt';
        }
        if (str_contains($lower, 'anxious') || str_contains($lower, 'worried') || str_contains($lower, 'stress')) {
            return 'overwhelmed';
        }

        return 'upset';
    }

    protected function inferNeed(string $message): string
    {
        $lower = mb_strtolower($message);

        if (str_contains($lower, 'late') || str_contains($lower, 'plan')) {
            return 'reliability';
        }
        if (str_contains($lower, 'ignore') || str_contains($lower, 'heard') || str_contains($lower, 'listen')) {
            return 'to feel heard';
        }
        if (str_contains($lower, 'chores') || str_contains($lower, 'task')) {
            return 'shared support';
        }

        return 'clarity and connection';
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
