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
            '3B6BE3AAA0B3681257D24C0B207082380108E21F577C5FCBDF4AB029CC7276D0',
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
                    '008605D603AFE833AB7ED3D2D0269764A4F607D26480306276A7EEC1CFFEA75B',
                ),
            ],
            'armeabi-v7a' => [
                'path' => public_path('downloads/omc-poltava-admin-armv7.apk'),
                'url' => rtrim((string) env('APP_URL', 'https://omc.pl.ua'), '/').'/downloads/omc-poltava-admin-armv7.apk',
                'sha256' => env(
                    'MOBILE_ANDROID_ARMV7_SHA256',
                    '268FFD24911FCCF0379EE10D66F6AC5D340392B632D84CCD96FCF1418D99E6F6',
                ),
            ],
            'x86_64' => [
                'path' => public_path('downloads/omc-poltava-admin-x86_64.apk'),
                'url' => rtrim((string) env('APP_URL', 'https://omc.pl.ua'), '/').'/downloads/omc-poltava-admin-x86_64.apk',
                'sha256' => env(
                    'MOBILE_ANDROID_X86_64_SHA256',
                    '40201582EAA56AF1A5DA70E823EDE07DA14502E085B908D4AE07083DC4BD8A71',
                ),
            ],
        ],
        'ios_url' => env('MOBILE_IOS_DOWNLOAD_URL'),
    ],
    'updates' => [
        'android' => [
            'latest_version' => env('MOBILE_ANDROID_LATEST_VERSION', '1.0.4'),
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
