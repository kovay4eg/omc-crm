<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AndroidAppDownloadController extends Controller
{
    public function __invoke(Request $request): BinaryFileResponse|Response
    {
        $latestBuild = (int) config('mobile.updates.android.latest_build_number', 0);
        $installedBuild = filter_var(
            $request->query('installed_build'),
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]],
        );

        if ($latestBuild > 0 && is_int($installedBuild) && $installedBuild >= $latestBuild) {
            return response('У вас уже встановлена актуальна або новіша версія застосунку.', 409)
                ->header('Content-Type', 'text/plain; charset=UTF-8')
                ->header('Cache-Control', 'private, no-store, max-age=0')
                ->header('X-Content-Type-Options', 'nosniff');
        }

        $path = config('mobile.downloads.android_path');
        $abi = strtolower((string) $request->query('abi'));
        $variants = config('mobile.downloads.android_variants', []);

        if (is_array($variants) && isset($variants[$abi]) && is_array($variants[$abi])) {
            $variantPath = $variants[$abi]['path'] ?? null;
            if (is_string($variantPath) && is_file($variantPath)) {
                $path = $variantPath;
            }
        }

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
