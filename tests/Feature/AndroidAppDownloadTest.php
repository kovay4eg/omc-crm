<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class AndroidAppDownloadTest extends TestCase
{
    private string $apkPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apkPath = storage_path('framework/testing/omc-poltava-admin.apk');
        config([
            'mobile.downloads.android_path' => $this->apkPath,
            'mobile.downloads.android_url' => null,
            'mobile.downloads.android_variants' => [],
        ]);
    }

    protected function tearDown(): void
    {
        File::delete($this->apkPath);

        parent::tearDown();
    }

    public function test_android_download_redirects_to_static_litespeed_file(): void
    {
        File::ensureDirectoryExists(dirname($this->apkPath));
        File::put($this->apkPath, 'test-apk');
        config(['mobile.downloads.android_url' => 'https://omc.pl.ua/downloads/omc-poltava-admin.apk']);

        $this->get('/download/android')
            ->assertRedirect('https://omc.pl.ua/downloads/omc-poltava-admin.apk');
    }

    public function test_android_download_selects_requested_architecture(): void
    {
        File::ensureDirectoryExists(dirname($this->apkPath));
        File::put($this->apkPath, 'test-apk');
        config([
            'mobile.downloads.android_variants.arm64-v8a' => [
                'path' => $this->apkPath,
                'url' => 'https://omc.pl.ua/downloads/omc-poltava-admin-arm64.apk',
            ],
        ]);

        $this->get('/download/android?abi=arm64-v8a')
            ->assertRedirect('https://omc.pl.ua/downloads/omc-poltava-admin-arm64.apk');
    }

    public function test_android_download_uses_a_stable_public_url(): void
    {
        File::ensureDirectoryExists(dirname($this->apkPath));
        File::put($this->apkPath, 'test-apk');

        $response = $this->get('/download/android');

        $response->assertOk()
            ->assertDownload('omc-poltava-admin.apk')
            ->assertHeader('content-type', 'application/vnd.android.package-archive');
    }

    public function test_android_download_reports_when_the_file_is_not_ready(): void
    {
        File::delete($this->apkPath);

        $this->get('/download/android')
            ->assertStatus(503)
            ->assertSeeText('Актуальна Android-версія готується до публікації.');
    }
}
