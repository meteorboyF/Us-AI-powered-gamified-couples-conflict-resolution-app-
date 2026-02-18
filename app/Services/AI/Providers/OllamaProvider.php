<?php

namespace App\Services\AI\Providers;

use App\Services\AI\Contracts\AiProvider;
use App\Services\AI\DTO\AiProviderResponse;
use App\Services\AI\Exceptions\AiProviderException;
use Illuminate\Support\Facades\Http;
use Throwable;

class OllamaProvider implements AiProvider
{
    public function chat(array $messages, array $options = []): AiProviderResponse
    {
        $baseUrl = rtrim((string) config('us.ai.providers.ollama.base_url', 'http://127.0.0.1:11434'), '/');
        $model = (string) config('us.ai.providers.ollama.model', 'llama3.1:8b');

        try {
            $response = Http::timeout(30)
                ->acceptJson()
                ->post($baseUrl.'/api/chat', [
                    'model' => $model,
                    'stream' => false,
                    'messages' => $messages,
                ]);
        } catch (Throwable $e) {
            throw new AiProviderException('Ollama request failed.', 0, $e);
        }

        if (! $response->successful()) {
            throw new AiProviderException('Ollama returned an error status: '.$response->status());
        }

        $data = $response->json();
        $text = (string) data_get($data, 'message.content', '');

        if ($text === '') {
            throw new AiProviderException('Ollama returned an empty response.');
        }

        return new AiProviderResponse(
            text: $text,
            raw: [],
            tokensIn: data_get($data, 'prompt_eval_count'),
            tokensOut: data_get($data, 'eval_count'),
            safety: [],
        );
    }
}
