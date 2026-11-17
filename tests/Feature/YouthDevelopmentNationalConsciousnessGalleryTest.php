<?php

namespace Tests\Feature;

use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class YouthDevelopmentNationalConsciousnessGalleryTest extends TestCase
{
    use RefreshDatabase;

    public function test_youth_development_department_uses_ordered_unique_gallery_without_department_cover(): void
    {
        Department::create([
            'name' => 'розвитку молоді та національної свідомості',
            'photo' => 'team/departments/youth-cover-that-must-not-render.webp',
            'slider_enabled' => false,
            'slider_photos' => ['team/departments/database-slide-that-must-not-render.webp'],
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('data-gallery="youth-development-national-consciousness"', false);
        $response->assertSee('data-department-photo-carousel', false);
        $response->assertSee('data-innovation-lightbox', false);
        $response->assertDontSee('youth-cover-that-must-not-render.webp', false);
        $response->assertDontSee('database-slide-that-must-not-render.webp', false);
        $this->assertSame(
            13,
            substr_count($response->getContent(), 'class="innovation-gallery__slide"'),
        );

        $orderedPaths = [];

        foreach (range(1, 13) as $number) {
            $file = sprintf('youth-development-national-consciousness-%02d.webp', $number);
            $relativePath = "images/team/departments/youth-development-national-consciousness/{$file}";
            $absolutePath = public_path($relativePath);
            $orderedPaths[] = $relativePath;

            $response->assertSee($relativePath, false);
            $this->assertFileExists($absolutePath);

            [$width, $height, $type] = getimagesize($absolutePath);
            $this->assertSame(1200, $width);
            $this->assertSame(900, $height);
            $this->assertSame(IMAGETYPE_WEBP, $type);
        }

        $response->assertSeeInOrder($orderedPaths, false);

        $this->assertCount(
            13,
            glob(public_path('images/team/departments/youth-development-national-consciousness/youth-development-national-consciousness-[0-9][0-9].webp')),
        );

        $manifest = json_decode(
            file_get_contents(public_path('images/team/departments/youth-development-national-consciousness/source-manifest.json')),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        $this->assertSame(
            [
                'youth-development-national-consciousness-01.webp' => 'IMG_2944.JPG',
                'youth-development-national-consciousness-02.webp' => 'IMG_3026.JPG',
                'youth-development-national-consciousness-03.webp' => 'IMG_3112.JPG',
                'youth-development-national-consciousness-04.webp' => 'IMG_3072.JPG',
                'youth-development-national-consciousness-05.webp' => 'IMG_3011.JPG',
                'youth-development-national-consciousness-06.webp' => 'IMG_3073.JPG',
                'youth-development-national-consciousness-07.webp' => 'IMG_2999.JPG',
                'youth-development-national-consciousness-08.webp' => 'IMG_2839.JPG',
                'youth-development-national-consciousness-09.webp' => 'IMG_3137.JPG',
                'youth-development-national-consciousness-10.webp' => 'IMG_2991.JPG',
                'youth-development-national-consciousness-11.webp' => 'IMG_2957.JPG',
                'youth-development-national-consciousness-12.webp' => 'IMG_2834.JPG',
                'youth-development-national-consciousness-13.webp' => 'IMG_2827.JPG',
            ],
            $manifest,
        );
        $this->assertCount(13, array_unique(array_values($manifest)));
    }

    public function test_youth_development_gallery_recognizes_department_prefix(): void
    {
        Department::create([
            'name' => 'Відділ розвитку молоді та національної свідомості',
            'slider_enabled' => false,
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('data-gallery="youth-development-national-consciousness"', false);
        $this->assertSame(
            13,
            substr_count($response->getContent(), 'class="innovation-gallery__slide"'),
        );
    }

    public function test_youth_development_gallery_assets_are_not_mixed_into_other_departments(): void
    {
        Department::create([
            'name' => 'Відділ стратегічного розвитку та партнерств',
            'slider_enabled' => true,
            'slider_photos' => ['team/departments/other-department.webp'],
        ]);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('data-gallery="youth-development-national-consciousness"', false)
            ->assertDontSee('images/team/departments/youth-development-national-consciousness/', false)
            ->assertSee('team/departments/1/slides/0', false);
    }

    public function test_all_fixed_department_galleries_coexist(): void
    {
        Department::create([
            'name' => 'Відділ розвитку молоді та національної свідомості',
            'slider_enabled' => false,
        ]);
        Department::create([
            'name' => 'Відділ інновацій та супроводу проєктів',
            'slider_enabled' => false,
        ]);
        Department::create([
            'name' => 'Відділ неформальної освіти та розвитку кар’єри',
            'slider_enabled' => false,
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('data-gallery="youth-development-national-consciousness"', false);
        $response->assertSee('data-gallery="innovation-projects"', false);
        $response->assertSee('data-gallery="nonformal-education-career"', false);
        $this->assertSame(
            57,
            substr_count($response->getContent(), 'class="innovation-gallery__slide"'),
        );
    }
}
