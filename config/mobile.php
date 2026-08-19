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
        'android_sha256' => env(
            'MOBILE_ANDROID_APK_SHA256',
            '82BC7B1B13266CCDC8E07ED79D2CE1D2F43C78F0AD1AAE72089F836F1D3FA8BE',
        ),
        'android_signing_sha256' => env(
            'MOBILE_ANDROID_SIGNING_SHA256',
            '1464D8E5415F2E7EA221FB8399C2E2A2E5C8D6956F3850F6A10E9520C265E364',
        ),
        'android_variants' => [
            'arm64-v8a' => [
                'path' => public_path('downloads/omc-poltava-admin-arm64.apk'),
                'url' => rtrim((string) env('APP_URL', 'https://omc.pl.ua'), '/').'/downloads/omc-poltava-admin-arm64.apk',
                'sha256' => env(
                    'MOBILE_ANDROID_ARM64_SHA256',
                    '2AF149FF1B47E7F2B315BD65B436F81529D5EB01C76527D8FE41FB2AE162BE54',
                ),
            ],
            'armeabi-v7a' => [
                'path' => public_path('downloads/omc-poltava-admin-armv7.apk'),
                'url' => rtrim((string) env('APP_URL', 'https://omc.pl.ua'), '/').'/downloads/omc-poltava-admin-armv7.apk',
                'sha256' => env(
                    'MOBILE_ANDROID_ARMV7_SHA256',
                    '302670123F7A3B9C4509A9B833ECB044F8D692B85154AE7C9AAC4600135E738B',
                ),
            ],
            'x86_64' => [
                'path' => public_path('downloads/omc-poltava-admin-x86_64.apk'),
                'url' => rtrim((string) env('APP_URL', 'https://omc.pl.ua'), '/').'/downloads/omc-poltava-admin-x86_64.apk',
                'sha256' => env(
                    'MOBILE_ANDROID_X86_64_SHA256',
                    '7E43D4311003A243CC0EF245243B57B243A7B680C6F8A6B673F91BA390C05D0F',
                ),
            ],
        ],
        'ios_url' => env('MOBILE_IOS_DOWNLOAD_URL'),
    ],
    'updates' => [
        'android' => [
            'latest_version' => env('MOBILE_ANDROID_LATEST_VERSION', '1.0.9'),
            'latest_build_number' => (int) env('MOBILE_ANDROID_LATEST_BUILD_NUMBER', 10),
            'minimum_version' => env('MOBILE_ANDROID_MINIMUM_VERSION', '1.0.0'),
            'minimum_build_number' => (int) env('MOBILE_ANDROID_MINIMUM_BUILD_NUMBER', 1),
            'update_url' => env('MOBILE_ANDROID_UPDATE_URL', 'https://omc.pl.ua/download/android'),
        ],
        'ios' => [
            'latest_version' => env('MOBILE_IOS_LATEST_VERSION', '1.0.0'),
            'latest_build_number' => (int) env('MOBILE_IOS_LATEST_BUILD_NUMBER', 1),
            'minimum_version' => env('MOBILE_IOS_MINIMUM_VERSION', '1.0.0'),
            'minimum_build_number' => (int) env('MOBILE_IOS_MINIMUM_BUILD_NUMBER', 1),
            'update_url' => env('MOBILE_IOS_UPDATE_URL', 'https://omc.pl.ua/download/ios'),
        ],
    ],
];
