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
            'C5E943D52FDF2B177677C3D497082366A88D5AE396D3A4B016DB61274397277A',
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
                    'A7FDB1DDF3AAD04A61C18E3626FC88DE980D7E6B94BD160C0CAE649AE5B3C539',
                ),
            ],
            'armeabi-v7a' => [
                'path' => public_path('downloads/omc-poltava-admin-armv7.apk'),
                'url' => rtrim((string) env('APP_URL', 'https://omc.pl.ua'), '/').'/downloads/omc-poltava-admin-armv7.apk',
                'sha256' => env(
                    'MOBILE_ANDROID_ARMV7_SHA256',
                    'EE0DA56F3501A0B463E8C3C373960D3C30581370F2EE7AAAE3CED783ADEFA93E',
                ),
            ],
            'x86_64' => [
                'path' => public_path('downloads/omc-poltava-admin-x86_64.apk'),
                'url' => rtrim((string) env('APP_URL', 'https://omc.pl.ua'), '/').'/downloads/omc-poltava-admin-x86_64.apk',
                'sha256' => env(
                    'MOBILE_ANDROID_X86_64_SHA256',
                    '04CBCF52463A23B319D074AE8575552F498DE2BCCF9F23E7034646E53ED48810',
                ),
            ],
        ],
        'ios_url' => env('MOBILE_IOS_DOWNLOAD_URL'),
    ],
    'updates' => [
        'android' => [
            'latest_version' => env('MOBILE_ANDROID_LATEST_VERSION', '1.0.10'),
            'latest_build_number' => (int) env('MOBILE_ANDROID_LATEST_BUILD_NUMBER', 11),
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
