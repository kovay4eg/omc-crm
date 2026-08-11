<?php

return [
    'address' => env('ADMIN_PRO_MAIL_ADDRESS', 'post@omc.pl.ua'),
    'name' => env('ADMIN_PRO_MAIL_NAME', 'Обласний молодіжний центр'),
    'username' => env('ADMIN_PRO_MAIL_USERNAME', 'post@omc.pl.ua'),
    'password' => env('ADMIN_PRO_MAIL_PASSWORD'),

    'imap' => [
        'host' => env('ADMIN_PRO_MAIL_IMAP_HOST', 'omc.pl.ua'),
        'port' => (int) env('ADMIN_PRO_MAIL_IMAP_PORT', 993),
        'encryption' => env('ADMIN_PRO_MAIL_IMAP_ENCRYPTION', 'ssl'),
        'validate_certificate' => env('ADMIN_PRO_MAIL_VALIDATE_CERTIFICATE', true),
    ],
];
