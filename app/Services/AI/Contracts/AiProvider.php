<?php

namespace App\Services\AI\Contracts;

use App\Services\AI\DTO\AiProviderResponse;

interface AiProvider
{
    /**
     * @param  array<int, array<string, string>>  $messages
     * @param  array<string, mixed>  $options
     */
    public function chat(array $messages, array $options = []): AiProviderResponse;
}
