<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AndroidAppDownloadController extends Controller
{
    public function __invoke(): BinaryFileResponse|Response
    {
        $path = config('mobile.downloads.android_path');

        if (! is_string($path) || ! is_file($path)) {
            return response('Актуальна Android-версія готується до публікації.', 503)
                ->header('Content-Type', 'text/plain; charset=UTF-8')
                ->header('Retry-After', '3600');
        }

        return response()->download(
            $path,
            'omc-poltava-admin.apk',
            [
                'Content-Type' => 'application/vnd.android.package-archive',
                'Cache-Control' => 'private, no-store, max-age=0',
                'X-Content-Type-Options' => 'nosniff',
            ],
        );
    }
}
