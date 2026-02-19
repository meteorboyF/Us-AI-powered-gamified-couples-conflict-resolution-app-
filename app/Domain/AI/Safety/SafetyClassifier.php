<?php

namespace App\Domain\AI\Safety;

class SafetyClassifier
{
    /**
     * @return array{flags: array<string, bool>, system_prompt: string}
     */
    public function classify(string $input): array
    {
        $text = mb_strtolower($input);

        $flags = [
            'high_conflict' => $this->containsAny($text, ['hate you', 'divorce now', 'ruin you', 'screw you']),
            'violence' => $this->containsAny($text, ['hit', 'hurt you', 'kill', 'violence']),
            'self_harm' => $this->containsAny($text, ['hurt myself', 'end it all', 'suicide', 'kill myself']),
        ];

        $system = 'You are not a therapist; you are a communication coach. Do not give medical/legal advice.';

        if (in_array(true, $flags, true)) {
            $system .= ' Encourage a pause, de-escalation, and suggest contacting local emergency/support services if immediate danger is present.';
        }

        return [
            'flags' => $flags,
            'system_prompt' => $system,
        ];
    }

    /**
     * @param  list<string>  $needles
     */
    private function containsAny(string $text, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (str_contains($text, $needle)) {
                return true;
            }
        }

        return false;
    }
}
