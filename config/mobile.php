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
            'CFDB38A9A33104598930D54E32CE6B2CC3433DEE52D07DFF0E9877B1FB3C2643',
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
                    'CFDB38A9A33104598930D54E32CE6B2CC3433DEE52D07DFF0E9877B1FB3C2643',
                ),
            ],
            'armeabi-v7a' => [
                'path' => public_path('downloads/omc-poltava-admin-armv7.apk'),
                'url' => rtrim((string) env('APP_URL', 'https://omc.pl.ua'), '/').'/downloads/omc-poltava-admin-armv7.apk',
                'sha256' => env(
                    'MOBILE_ANDROID_ARMV7_SHA256',
                    '79B235FD7843CC1EFDE0CF1304F7999A688E31687E830F6992A3864D53EEC617',
                ),
            ],
            'x86_64' => [
                'path' => public_path('downloads/omc-poltava-admin-x86_64.apk'),
                'url' => rtrim((string) env('APP_URL', 'https://omc.pl.ua'), '/').'/downloads/omc-poltava-admin-x86_64.apk',
                'sha256' => env(
                    'MOBILE_ANDROID_X86_64_SHA256',
                    '0A01FA697B820B3DC630C7E7DA939FDAA06A01D1DC3785E99E75720CFA95AB3C',
                ),
            ],
        ],
        'ios_url' => env('MOBILE_IOS_DOWNLOAD_URL'),
    ],
    'updates' => [
        'android' => [
            'latest_version' => env('MOBILE_ANDROID_LATEST_VERSION', '1.0.8'),
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
