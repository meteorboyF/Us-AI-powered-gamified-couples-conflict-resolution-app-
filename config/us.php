<?php

return [
    'features' => [
        'chat_v1' => env('US_FEATURE_CHAT_V1', true),
    ],

    'chat' => [
        'attachments_disk' => env('US_CHAT_ATTACHMENTS_DISK', 'public'),
        'max_message_len' => (int) env('US_CHAT_MAX_MESSAGE_LEN', 2000),
        'max_file_mb' => (int) env('US_CHAT_MAX_FILE_MB', 10),
        'rate_limit_per_minute' => (int) env('US_CHAT_RATE_LIMIT_PER_MINUTE', 20),
    ],
];
