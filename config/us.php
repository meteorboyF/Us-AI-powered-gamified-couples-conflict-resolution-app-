<?php

return [
    'features' => [
        'chat_v1' => env('US_FEATURE_CHAT_V1', true),
        'vault_v1' => env('US_FEATURE_VAULT_V1', true),
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
];
