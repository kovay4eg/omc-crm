<?php

return [
    'downloads' => [
        'android_path' => env(
            'MOBILE_ANDROID_APK_PATH',
            public_path('downloads/omc-poltava-admin.apk'),
        ),
        'android_url' => env(
            'MOBILE_ANDROID_APK_URL',
            rtrim((string) env('APP_URL', 'https://omc.pl.ua'), '/').'/downloads/omc-poltava-admin.apk',
        ),
        'android_variants' => [
            'arm64-v8a' => [
                'path' => public_path('downloads/omc-poltava-admin-arm64.apk'),
                'url' => rtrim((string) env('APP_URL', 'https://omc.pl.ua'), '/').'/downloads/omc-poltava-admin-arm64.apk',
            ],
            'armeabi-v7a' => [
                'path' => public_path('downloads/omc-poltava-admin-armv7.apk'),
                'url' => rtrim((string) env('APP_URL', 'https://omc.pl.ua'), '/').'/downloads/omc-poltava-admin-armv7.apk',
            ],
            'x86_64' => [
                'path' => public_path('downloads/omc-poltava-admin-x86_64.apk'),
                'url' => rtrim((string) env('APP_URL', 'https://omc.pl.ua'), '/').'/downloads/omc-poltava-admin-x86_64.apk',
            ],
        ],
        'ios_url' => env('MOBILE_IOS_DOWNLOAD_URL'),
    ],
    'updates' => [
        'android' => [
            'latest_version' => env('MOBILE_ANDROID_LATEST_VERSION', '1.0.3'),
            'minimum_version' => env('MOBILE_ANDROID_MINIMUM_VERSION', '1.0.0'),
            'update_url' => env('MOBILE_ANDROID_UPDATE_URL', 'https://omc.pl.ua/download/android'),
        ],
        'ios' => [
            'latest_version' => env('MOBILE_IOS_LATEST_VERSION', '1.0.0'),
            'minimum_version' => env('MOBILE_IOS_MINIMUM_VERSION', '1.0.0'),
            'update_url' => env('MOBILE_IOS_UPDATE_URL', 'https://omc.pl.ua/download/ios'),
        ],
    ],
];
