<?php

return [
    /*
    | Disabled until a dedicated, non-MFA OMC Cloud service account is set on
    | the server. No local file is removed merely by configuring this client.
    */
    'enabled' => (bool) env('OMC_MEDIA_ARCHIVE_ENABLED', false),

    'base_url' => env('OMC_MEDIA_ARCHIVE_BASE_URL', 'https://pomc.com.ua'),
    'login' => env('OMC_MEDIA_ARCHIVE_LOGIN'),
    'password' => env('OMC_MEDIA_ARCHIVE_PASSWORD'),
    'remote_root' => trim((string) env('OMC_MEDIA_ARCHIVE_ROOT', 'omc-crm-archive'), '/'),
    'timeout' => (int) env('OMC_MEDIA_ARCHIVE_TIMEOUT', 120),
    'connect_timeout' => (int) env('OMC_MEDIA_ARCHIVE_CONNECT_TIMEOUT', 15),
    'verify_tls' => (bool) env('OMC_MEDIA_ARCHIVE_VERIFY_TLS', true),
    'session_ttl_seconds' => (int) env('OMC_MEDIA_ARCHIVE_SESSION_TTL', 25200),
    'small_upload_bytes' => (int) env('OMC_MEDIA_ARCHIVE_SMALL_UPLOAD_BYTES', 25165824),
    'chunk_bytes' => 4 * 1024 * 1024,

    'thresholds' => [
        'trigger_percent' => (float) env('OMC_MEDIA_ARCHIVE_TRIGGER_PERCENT', 10),
        'recovery_percent' => (float) env('OMC_MEDIA_ARCHIVE_RECOVERY_PERCENT', 15),
        'trigger_bytes' => (int) env('OMC_MEDIA_ARCHIVE_TRIGGER_BYTES', 2 * 1024 * 1024 * 1024),
        'recovery_bytes' => (int) env('OMC_MEDIA_ARCHIVE_RECOVERY_BYTES', 4 * 1024 * 1024 * 1024),
    ],

    'retention' => [
        'orphan_grace_days' => (int) env('OMC_MEDIA_ARCHIVE_ORPHAN_GRACE_DAYS', 7),
        'announcement_days' => (int) env('OMC_MEDIA_ARCHIVE_ANNOUNCEMENT_DAYS', 90),
        'event_days' => (int) env('OMC_MEDIA_ARCHIVE_EVENT_DAYS', 180),
    ],

    'batch_size' => (int) env('OMC_MEDIA_ARCHIVE_BATCH_SIZE', 200),
    'stale_upload_minutes' => (int) env('OMC_MEDIA_ARCHIVE_STALE_UPLOAD_MINUTES', 30),
];
