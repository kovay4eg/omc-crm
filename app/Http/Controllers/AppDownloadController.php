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
            'iosDownloadUrl' => route('app.download.ios'),
            'iosVersion' => config('mobile.updates.ios.latest_version'),
            'iosAvailable' => is_string($iosUrl) && filter_var($iosUrl, FILTER_VALIDATE_URL),
        ]);
    }
}
