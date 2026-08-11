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
            '48442DE087DC445DFC719DEC2DD76502E58B0099D2101B5E425934F203DACE89',
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
                    '48442DE087DC445DFC719DEC2DD76502E58B0099D2101B5E425934F203DACE89',
                ),
            ],
            'armeabi-v7a' => [
                'path' => public_path('downloads/omc-poltava-admin-armv7.apk'),
                'url' => rtrim((string) env('APP_URL', 'https://omc.pl.ua'), '/').'/downloads/omc-poltava-admin-armv7.apk',
                'sha256' => env(
                    'MOBILE_ANDROID_ARMV7_SHA256',
                    '48442DE087DC445DFC719DEC2DD76502E58B0099D2101B5E425934F203DACE89',
                ),
            ],
            'x86_64' => [
                'path' => public_path('downloads/omc-poltava-admin-x86_64.apk'),
                'url' => rtrim((string) env('APP_URL', 'https://omc.pl.ua'), '/').'/downloads/omc-poltava-admin-x86_64.apk',
                'sha256' => env(
                    'MOBILE_ANDROID_X86_64_SHA256',
                    '48442DE087DC445DFC719DEC2DD76502E58B0099D2101B5E425934F203DACE89',
                ),
            ],
        ],
        'ios_url' => env('MOBILE_IOS_DOWNLOAD_URL'),
    ],
    'updates' => [
        'android' => [
            'latest_version' => env('MOBILE_ANDROID_LATEST_VERSION', '1.0.7'),
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
