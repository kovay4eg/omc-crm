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
            'androidVersion' => config('mobile.updates.android.latest_version'),
            'androidApkSha256' => config('mobile.downloads.android_sha256'),
            'androidSigningSha256' => config('mobile.downloads.android_signing_sha256'),
            'iosDownloadUrl' => route('app.download.ios'),
            'iosVersion' => config('mobile.updates.ios.latest_version'),
            'iosAvailable' => is_string($iosUrl) && filter_var($iosUrl, FILTER_VALIDATE_URL),
        ]);
    }
}
