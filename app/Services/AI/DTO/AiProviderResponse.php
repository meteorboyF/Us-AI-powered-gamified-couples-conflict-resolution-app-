<?php

namespace App\Services\AI\DTO;

class AiProviderResponse
{
    /**
     * @param  array<string, mixed>  $raw
     * @param  array<string, mixed>  $safety
     */
    public function __construct(
        public string $text,
        public array $raw = [],
        public ?int $tokensIn = null,
        public ?int $tokensOut = null,
        public array $safety = [],
    ) {}
}
