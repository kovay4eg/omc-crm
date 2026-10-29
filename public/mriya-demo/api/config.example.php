<?php
// Скопіюйте як config.php або задайте ці значення через змінні середовища.
return [
    'admin_email' => getenv('DEMO_ADMIN_EMAIL') ?: 'your-email@example.com',
    'admin_token' => getenv('DEMO_ADMIN_TOKEN') ?: 'CHANGE_THIS_LONG_RANDOM_TOKEN',
    'data_dir' => getenv('DEMO_DATA_DIR') ?: dirname(__DIR__) . '/data',
    'mail_from' => getenv('DEMO_MAIL_FROM') ?: 'demo@example.com',
];
