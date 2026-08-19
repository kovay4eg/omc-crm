<?php

namespace Tests\Feature;

use Tests\TestCase;

class AppDownloadTest extends TestCase
{
    public function test_android_download_is_streamed_without_cacheable_redirect(): void
    {
        $apk = tempnam(sys_get_temp_dir(), 'omc-apk-');
        file_put_contents($apk, 'current-apk');

        try {
            config()->set('mobile.downloads.android_path', $apk);
            config()->set('mobile.downloads.android_url', 'https://omc.pl.ua/downloads/old.apk');
            config()->set('mobile.downloads.android_variants', []);

            $response = $this->get('/download/android');

            $response->assertOk();
            $response->assertHeader('Content-Type', 'application/vnd.android.package-archive');
            $response->assertHeader('X-Content-Type-Options', 'nosniff');
            $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
            $this->assertStringContainsString('omc-poltava-admin.apk', (string) $response->headers->get('Content-Disposition'));
        } finally {
            @unlink($apk);
        }
    }

    public function test_android_download_uses_requested_local_abi_variant(): void
    {
        $universal = tempnam(sys_get_temp_dir(), 'omc-universal-');
        $arm64 = tempnam(sys_get_temp_dir(), 'omc-arm64-');
        file_put_contents($universal, 'universal');
        file_put_contents($arm64, 'arm64-current');

        try {
            config()->set('mobile.downloads.android_path', $universal);
            config()->set('mobile.downloads.android_variants.arm64-v8a.path', $arm64);

            $response = $this->get('/download/android?abi=arm64-v8a');

            $response->assertOk();
            $this->assertSame(
                realpath($arm64),
                realpath($response->baseResponse->getFile()->getPathname()),
            );
        } finally {
            @unlink($universal);
            @unlink($arm64);
        }
    }

    public function test_android_download_rejects_same_or_newer_installed_build(): void
    {
        config()->set('mobile.updates.android.latest_build_number', 10);

        $response = $this->get('/download/android?installed_build=10');

        $response->assertStatus(409);
        $this->assertSame(
            'У вас уже встановлена актуальна або новіша версія застосунку.',
            $response->getContent(),
        );
        $this->assertStringContainsString(
            'no-store',
            (string) $response->headers->get('Cache-Control'),
        );
    }
}
