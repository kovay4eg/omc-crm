<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AndroidAppDownloadController extends Controller
{
    public function __invoke(Request $request): BinaryFileResponse|RedirectResponse|Response
    {
        $path = config('mobile.downloads.android_path');
        $url = config('mobile.downloads.android_url');
        $abi = strtolower((string) $request->query('abi'));
        $variants = config('mobile.downloads.android_variants', []);

        if (is_array($variants) && isset($variants[$abi]) && is_array($variants[$abi])) {
            $variantPath = $variants[$abi]['path'] ?? null;
            $variantUrl = $variants[$abi]['url'] ?? null;
            if (is_string($variantPath) && is_file($variantPath) && $this->isSafeUrl($variantUrl)) {
                $path = $variantPath;
                $url = $variantUrl;
            }
        }

        if (! is_string($path) || ! is_file($path)) {
            return response('Актуальна Android-версія готується до публікації.', 503)
                ->header('Content-Type', 'text/plain; charset=UTF-8')
                ->header('Retry-After', '3600');
        }

        if ($this->isSafeUrl($url)) {
            return redirect()->away($url)
                ->withHeaders([
                    'Cache-Control' => 'no-store, max-age=0',
                    'X-Content-Type-Options' => 'nosniff',
                ]);
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

    private function isSafeUrl(mixed $url): bool
    {
        if (! is_string($url) || filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        return parse_url($url, PHP_URL_SCHEME) === 'https';
    }
}
