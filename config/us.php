<?php

return [
    'features' => [
        'chat_v1' => env('US_FEATURE_CHAT_V1', true),
        'vault_v1' => env('US_FEATURE_VAULT_V1', true),
        'ai_coach_v1' => env('US_FEATURE_AI_COACH_V1', true),
    ],

    'chat' => [
        'attachments_disk' => env('US_CHAT_ATTACHMENTS_DISK', 'public'),
        'max_message_len' => (int) env('US_CHAT_MAX_MESSAGE_LEN', 2000),
        'max_file_mb' => (int) env('US_CHAT_MAX_FILE_MB', 10),
        'rate_limit_per_minute' => (int) env('US_CHAT_RATE_LIMIT_PER_MINUTE', 20),
    ],

    'vault' => [
        'max_upload_mb' => (int) env('US_VAULT_MAX_UPLOAD_MB', 10),
        'sensitive_requires_consent_default' => (bool) env('US_VAULT_SENSITIVE_REQUIRES_CONSENT_DEFAULT', true),
        'unlock_window_minutes' => (int) env('US_VAULT_UNLOCK_WINDOW_MINUTES', 30),
    ],

    'ai' => [
        'default_provider' => env('US_AI_DEFAULT_PROVIDER', 'fake'),
        'max_input_chars' => (int) env('US_AI_MAX_INPUT_CHARS', 4000),
        'rate_limit_per_minute' => (int) env('US_AI_RATE_LIMIT_PER_MINUTE', 10),
        'providers' => [
            'ollama' => [
                'base_url' => env('OLLAMA_BASE_URL', 'http://127.0.0.1:11434'),
                'model' => env('OLLAMA_MODEL', 'llama3.1:8b'),
            ],
        ],
    ],
];
