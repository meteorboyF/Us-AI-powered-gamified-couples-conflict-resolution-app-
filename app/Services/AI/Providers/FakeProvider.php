<?php

namespace App\Services\AI\Providers;

use App\Services\AI\Contracts\AiProvider;
use App\Services\AI\DTO\AiProviderResponse;

class FakeProvider implements AiProvider
{
    public function chat(array $messages, array $options = []): AiProviderResponse
    {
        $mode = (string) ($options['mode'] ?? 'vent');

        $text = match ($mode) {
            'bridge' => "Draft Bridge Message:\nI hear you, and I want us to talk calmly. Could we pause and restart with respect?\nAsk before sending.",
            'repair' => "Repair Plan:\n1) Pause and breathe\n2) Name the issue\n3) Share needs\n4) Agree one next step\n5) Confirm check-in\nMicro-actions: water break, 10-minute walk, write one appreciation.\nCheck-in question: What felt better after this step?",
            default => "Vent Reflection:\nSummary: You are feeling intense pressure and want to be understood.\nQuestions: What happened right before this? What do you need right now? What can help you feel 10% calmer?\nGrounding: Take 5 slow breaths and relax your shoulders.",
        };

        return new AiProviderResponse(
            text: $text,
            raw: ['provider' => 'fake', 'mode' => $mode],
            tokensIn: 42,
            tokensOut: 64,
            safety: [],
        );
    }
}
