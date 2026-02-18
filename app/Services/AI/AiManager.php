<?php

namespace App\Services\AI;

use App\Services\AI\Contracts\AiProvider;
use App\Services\AI\Exceptions\AiProviderException;
use App\Services\AI\Providers\FakeProvider;
use App\Services\AI\Providers\OllamaProvider;

class AiManager
{
    public function provider(): AiProvider
    {
        $provider = (string) config('us.ai.default_provider', 'fake');

        return match ($provider) {
            'ollama' => app(OllamaProvider::class),
            'fake' => app(FakeProvider::class),
            default => throw new AiProviderException("Unsupported AI provider [{$provider}]."),
        };
    }
}
