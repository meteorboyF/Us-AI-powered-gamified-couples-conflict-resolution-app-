<?php

namespace App\Domain\AI\Prompts;

class PromptBuilder
{
    /**
     * @param  array<string, bool>  $safetyFlags
     * @return array<int, array<string, string>>
     */
    public function build(string $mode, string $userMessage, array $safetyFlags = []): array
    {
        $system = 'You are not a therapist; you are a communication coach. Do not give medical/legal advice.';

        if (in_array(true, $safetyFlags, true)) {
            $system .= ' Keep responses calm, de-escalating, and suggest seeking immediate local support when safety risk is present.';
        }

        $modeInstruction = match ($mode) {
            'bridge' => 'Rewrite the user message into respectful non-violent communication. Include "Ask before sending". Output plain text only.',
            'repair' => 'Return a 5-step repair plan, 3 micro-actions, and one check-in question. Keep concise.',
            default => 'Return a summary, exactly 3 reflective questions, and one grounding exercise suggestion.',
        };

        return [
            ['role' => 'system', 'content' => $system],
            ['role' => 'system', 'content' => 'Mode: '.$modeInstruction],
            ['role' => 'user', 'content' => $userMessage],
        ];
    }
}
