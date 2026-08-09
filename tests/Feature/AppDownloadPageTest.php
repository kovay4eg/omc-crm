<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppDownloadPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_app_download_page_contains_qr_and_direct_android_link(): void
    {
        $response = $this->get('/app');

        $response->assertOk()
            ->assertSeeText('Завантажити для Android')
            ->assertSee(route('app.download.android'))
            ->assertSee(asset('images/qr/omc-android-download-qr-square.png'));
    }
}
