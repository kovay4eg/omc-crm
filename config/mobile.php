<?php

return [
    'downloads' => [
        'android_path' => env(
            'MOBILE_ANDROID_APK_PATH',
            public_path('downloads/omc-poltava-admin.apk'),
        ),
        'ios_url' => env('MOBILE_IOS_DOWNLOAD_URL'),
    ],
    'updates' => [
        'android' => [
            'latest_version' => env('MOBILE_ANDROID_LATEST_VERSION', '1.0.1'),
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
