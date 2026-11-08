<?php

namespace Tests\Feature;

use App\Http\Resources\Api\V1\HomepageSettingResource;
use App\Models\HomepageSetting;
use App\Support\BrandLogo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class BrandLogoTest extends TestCase
{
    use RefreshDatabase;

    public function test_canonical_logo_is_a_safe_omc_vector_asset(): void
    {
        $path = public_path(BrandLogo::ASSET_PATH);
        $markup = file_get_contents($path);

        $this->assertIsString($markup);
        $this->assertStringContainsString('data-brand-logo="poltava-omc"', $markup);
        $this->assertStringContainsString('Обласний молодіжний центр Полтавської обласної ради', $markup);
        $this->assertMatchesRegularExpression('/<svg\b[^>]*\bviewBox="[^"]+"/i', $markup);
        $this->assertGreaterThanOrEqual(50, substr_count($markup, '<path'));

        foreach (['<image', 'data:image', 'base64', '<text', '<foreignObject', 'href="http', '<script'] as $forbidden) {
            $this->assertStringNotContainsString($forbidden, $markup);
        }

        $hash = hash_file('sha256', $path);

        $this->assertIsString($hash);
        $this->assertStringEndsWith('?v='.substr($hash, 0, 12), BrandLogo::url());
    }

    public function test_public_pages_use_the_canonical_logo_when_no_custom_logo_is_configured(): void
    {
        foreach (['/', '/app', '/privacy-policy', '/support/omc-crm', '/account-deletion'] as $uri) {
            $this->get($uri)
                ->assertOk()
                ->assertSee(BrandLogo::ASSET_PATH, false);
        }
    }

    public function test_custom_homepage_logo_still_overrides_the_canonical_fallback(): void
    {
        HomepageSetting::query()->create([
            'logo' => 'homepage/custom-logo.svg',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('media/homepage/custom-logo.svg', false)
            ->assertDontSee(BrandLogo::ASSET_PATH, false);
    }

    public function test_legacy_uploaded_default_is_replaced_by_the_canonical_vector_everywhere(): void
    {
        $settings = HomepageSetting::query()->create([
            'logo' => 'homepage/Logo_1 1.svg',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee(BrandLogo::ASSET_PATH, false)
            ->assertDontSee('media/homepage/Logo_1%201.svg', false);

        $resource = new HomepageSettingResource($settings);
        $payload = $resource->toArray(Request::create('/api/v1/homepage-settings'));

        $this->assertSame(BrandLogo::url(), $payload['logo_url']);
    }
}
