<?php

namespace Tests\Feature;

use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NonformalEducationCareerGalleryTest extends TestCase
{
    use RefreshDatabase;

    public function test_nonformal_education_career_department_uses_ordered_gallery_without_department_cover(): void
    {
        Department::create([
            'name' => 'Відділ неформальної освіти та розвитку кар’єри',
            'photo' => 'team/departments/nonformal-cover.webp',
            'slider_enabled' => false,
            'slider_photos' => ['team/departments/database-slide-that-must-not-render.webp'],
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('data-gallery="nonformal-education-career"', false);
        $response->assertSee('data-department-photo-carousel', false);
        $response->assertSee('data-innovation-lightbox', false);
        $response->assertDontSee('nonformal-cover.webp', false);
        $response->assertDontSee('database-slide-that-must-not-render.webp', false);
        $this->assertSame(
            20,
            substr_count($response->getContent(), 'class="innovation-gallery__slide"'),
        );

        $orderedPaths = [];

        foreach (range(1, 20) as $number) {
            $file = sprintf('nonformal-education-career-%02d.webp', $number);
            $relativePath = "images/team/departments/nonformal-education-career/{$file}";
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
            20,
            glob(public_path('images/team/departments/nonformal-education-career/nonformal-education-career-[0-9][0-9].webp')),
        );

        $this->assertSame(
            [
                'nonformal-education-career-01.webp' => 'IMG_2620.JPG',
                'nonformal-education-career-02.webp' => 'IMG_2619.JPG',
                'nonformal-education-career-03.webp' => 'IMG_2618.JPG',
                'nonformal-education-career-04.webp' => 'IMG_2616.JPG',
                'nonformal-education-career-05.webp' => 'IMG_2615.JPG',
                'nonformal-education-career-06.webp' => 'IMG_2614.JPG',
                'nonformal-education-career-07.webp' => 'IMG_2613.JPG',
                'nonformal-education-career-08.webp' => 'IMG_2611.JPG',
                'nonformal-education-career-09.webp' => 'IMG_2610.JPG',
                'nonformal-education-career-10.webp' => 'IMG_2609.JPG',
                'nonformal-education-career-11.webp' => 'IMG_2606.JPG',
                'nonformal-education-career-12.webp' => 'IMG_2605.JPG',
                'nonformal-education-career-13.webp' => 'IMG_2604.JPG',
                'nonformal-education-career-14.webp' => 'IMG_2603.JPG',
                'nonformal-education-career-15.webp' => 'IMG_2601.JPG',
                'nonformal-education-career-16.webp' => 'IMG_2600.JPG',
                'nonformal-education-career-17.webp' => 'IMG_2599.JPG',
                'nonformal-education-career-18.webp' => 'IMG_2598.JPG',
                'nonformal-education-career-19.webp' => 'IMG_2597.JPG',
                'nonformal-education-career-20.webp' => 'IMG_2595.JPG',
            ],
            json_decode(
                file_get_contents(public_path('images/team/departments/nonformal-education-career/source-manifest.json')),
                true,
                flags: JSON_THROW_ON_ERROR,
            ),
        );
    }

    public function test_nonformal_gallery_recognizes_a_straight_apostrophe_in_the_department_name(): void
    {
        Department::create([
            'name' => "Відділ неформальної освіти та розвитку кар'єри",
            'slider_enabled' => false,
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('data-gallery="nonformal-education-career"', false);
        $this->assertSame(
            20,
            substr_count($response->getContent(), 'class="innovation-gallery__slide"'),
        );
    }

    public function test_nonformal_gallery_ignores_trailing_non_breaking_spaces_from_existing_content(): void
    {
        Department::create([
            'name' => "неформальної освіти та розвитку кар’єри\u{00A0}\u{00A0}\u{00A0}",
            'slider_enabled' => false,
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('data-gallery="nonformal-education-career"', false);
        $this->assertSame(
            20,
            substr_count($response->getContent(), 'class="innovation-gallery__slide"'),
        );
    }

    public function test_nonformal_gallery_assets_are_not_mixed_into_other_departments(): void
    {
        Department::create([
            'name' => 'Відділ розвитку молоді',
            'slider_enabled' => true,
            'slider_photos' => ['team/departments/other-department.webp'],
        ]);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('data-gallery="nonformal-education-career"', false)
            ->assertDontSee('images/team/departments/nonformal-education-career/', false)
            ->assertSee('team/departments/1/slides/0', false);
    }

    public function test_both_fixed_department_galleries_coexist_without_department_covers(): void
    {
        Department::create([
            'name' => 'Відділ інновацій та супроводу проєктів',
            'photo' => 'team/departments/innovation-cover-that-must-not-render.webp',
            'slider_enabled' => false,
        ]);
        Department::create([
            'name' => 'Відділ неформальної освіти та розвитку кар’єри',
            'photo' => 'team/departments/nonformal-cover-that-must-not-render.webp',
            'slider_enabled' => false,
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('data-gallery="innovation-projects"', false);
        $response->assertSee('data-gallery="nonformal-education-career"', false);
        $response->assertDontSee('innovation-cover-that-must-not-render.webp', false);
        $response->assertDontSee('nonformal-cover-that-must-not-render.webp', false);
        $this->assertSame(
            44,
            substr_count($response->getContent(), 'class="innovation-gallery__slide"'),
        );
    }
}
