<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class AppDownloadController extends Controller
{
    public function __invoke(): View
    {
        $iosUrl = config('mobile.downloads.ios_url');

        return view('app-download', [
            'androidDownloadUrl' => route('app.download.android'),
            'androidRecommendedUrl' => route('app.download.android', ['abi' => 'arm64-v8a']),
            'androidVersion' => config('mobile.updates.android.latest_version'),
            'androidUniversalSha256' => config('mobile.downloads.android_sha256'),
            'androidApkSha256' => config('mobile.downloads.android_variants.arm64-v8a.sha256'),
            'androidSigningSha256' => config('mobile.downloads.android_signing_sha256'),
            'iosDownloadUrl' => route('app.download.ios'),
            'iosVersion' => config('mobile.updates.ios.latest_version'),
            'iosAvailable' => is_string($iosUrl) && filter_var($iosUrl, FILTER_VALIDATE_URL),
        ]);
    }
}
