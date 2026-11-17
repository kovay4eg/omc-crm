<?php

namespace Tests\Feature;

use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InnovationProjectsGalleryTest extends TestCase
{
    use RefreshDatabase;

    public function test_innovation_projects_department_uses_ordered_gallery_without_department_cover(): void
    {
        Department::create([
            'name' => 'Відділ інновацій та супроводу проєктів',
            'slider_enabled' => false,
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('data-gallery="innovation-projects"', false);
        $response->assertSee('data-innovation-projects-carousel', false);
        $response->assertSee('data-innovation-lightbox', false);
        $response->assertSee('data-innovation-lightbox-previous', false);
        $response->assertSee('data-innovation-lightbox-next', false);
        $response->assertDontSee('innovation-projects-cover.webp', false);
        $this->assertSame(
            24,
            substr_count($response->getContent(), 'class="innovation-gallery__slide"'),
        );

        $orderedPaths = [];

        foreach (range(1, 24) as $number) {
            $file = sprintf('innovation-projects-%02d.webp', $number);
            $relativePath = "images/team/departments/innovation-projects/{$file}";
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
            24,
            glob(public_path('images/team/departments/innovation-projects/innovation-projects-[0-9][0-9].webp')),
        );
    }

    public function test_innovation_projects_gallery_recognizes_the_current_database_spelling(): void
    {
        Department::create([
            'name' => 'Відділ іновацій та супроводу проектів',
            'slider_enabled' => false,
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('data-gallery="innovation-projects"', false);
        $response->assertDontSee('innovation-projects-cover.webp', false);
        $this->assertSame(
            24,
            substr_count($response->getContent(), 'class="innovation-gallery__slide"'),
        );
    }

    public function test_innovation_projects_assets_are_not_mixed_into_other_departments(): void
    {
        Department::create([
            'name' => 'Відділ розвитку молоді',
            'slider_enabled' => true,
            'slider_photos' => ['team/departments/other-department.webp'],
        ]);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('data-gallery="innovation-projects"', false)
            ->assertDontSee('images/team/departments/innovation-projects/', false)
            ->assertSee('team/departments/1/slides/0', false);
    }
}
