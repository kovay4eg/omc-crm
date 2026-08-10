<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppDownloadPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_app_download_page_contains_android_and_ios_links_and_qr_codes(): void
    {
        $response = $this->get('/app');

        $response->assertOk()
            ->assertSeeText('Завантажити для Android')
            ->assertSeeText('iOS готується до публікації')
            ->assertSee(route('app.download.android'))
            ->assertSee(route('app.download.ios'))
            ->assertSee(asset('images/qr/omc-android-download-qr-square.png'))
            ->assertSee(asset('images/qr/omc-ios-download-qr-square.png'));
    }
}
