<?php

namespace Tests\Feature;

use Tests\TestCase;

class IosAppDownloadTest extends TestCase
{
    public function test_ios_download_uses_permanent_app_section_until_store_url_is_configured(): void
    {
        config(['mobile.downloads.ios_url' => null]);

        $this->get('/download/ios')
            ->assertRedirect(route('app.download').'#ios');
    }

    public function test_ios_download_redirects_to_configured_store_url(): void
    {
        config(['mobile.downloads.ios_url' => 'https://apps.apple.com/app/id123456789']);

        $this->get('/download/ios')
            ->assertRedirect('https://apps.apple.com/app/id123456789');
    }
}
