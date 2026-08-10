<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

class IosAppDownloadController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        $url = config('mobile.downloads.ios_url');

        if (is_string($url) && filter_var($url, FILTER_VALIDATE_URL)) {
            return redirect()->away($url);
        }

        return redirect()->to(route('app.download').'#ios');
    }
}
