<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StructureGalleriesTest extends TestCase
{
    use RefreshDatabase;

    public function test_hub_and_career_cards_and_galleries_use_the_supplied_local_photos(): void
    {
        $response = $this->get('/');
        $response->assertOk();

        $html = $response->getContent();

        $response->assertSee('images/structure/hub/hub-cover.webp', false);
        $response->assertSee('images/structure/career/career-cover.webp', false);
        $response->assertSee('data-gallery-key="hub"', false);
        $response->assertSee('data-gallery-key="career"', false);

        $this->assertSame(3, substr_count($html, 'class="structure-carousel mb-5"'));
        $this->assertSame(38, substr_count($html, 'class="structure-carousel__slide"'));
        $this->assertSame(2, substr_count($html, 'class="red-block"'));
        $this->assertSame(
            1,
            substr_count($html, 'https://images.unsplash.com/photo-1522071820081-009f0129c71c'),
            'Локальні обкладинки мають замінити stock URL для Хабу й Кар’єрного центру; один URL лишається для мобільної роботи.',
        );

        $mobileStart = strpos($html, 'id="mobile-view"');
        $careerStart = strpos($html, 'id="career-view"');
        $bookingStart = strpos($html, 'class="booking-line', $careerStart);

        $this->assertNotFalse($mobileStart);
        $this->assertNotFalse($careerStart);
        $this->assertNotFalse($bookingStart);

        $mobileMarkup = substr($html, $mobileStart, $careerStart - $mobileStart);
        $careerMarkup = substr($html, $careerStart, $bookingStart - $careerStart);

        $this->assertStringNotContainsString('data-structure-carousel', $mobileMarkup);
        $this->assertSame(2, substr_count($mobileMarkup, 'class="red-block"'));
        $this->assertStringContainsString('data-gallery-key="career"', $careerMarkup);
        $this->assertStringNotContainsString('class="red-block"', $careerMarkup);

        $this->assertGalleryAssets('hub', 16);
        $this->assertGalleryAssets('career', 12);
    }

    public function test_source_manifests_preserve_the_approved_photo_order(): void
    {
        $this->assertSame(
            [
                'cover' => [
                    'source' => 'IMG_2438.JPG',
                    'output' => 'hub-cover.webp',
                ],
                'gallery' => $this->manifestEntries('hub', [
                    'IMG_2442.JPG',
                    'IMG_2471.JPG',
                    'IMG_2467.JPG',
                    'IMG_2472.JPG',
                    'IMG_2464.JPG',
                    'IMG_2463.JPG',
                    'IMG_2477.JPG',
                    'IMG_2475.JPG',
                    'IMG_2473.JPG',
                    'IMG_2457.JPG',
                    'IMG_2455.JPG',
                    'IMG_2459.JPG',
                    'IMG_2479.JPG',
                    'IMG_2446.JPG',
                    'IMG_2452.JPG',
                    'IMG_2451.JPG',
                ]),
            ],
            $this->readManifest('hub'),
        );

        $this->assertSame(
            [
                'cover' => [
                    'source' => 'IMG_2594.JPG',
                    'output' => 'career-cover.webp',
                ],
                'gallery' => $this->manifestEntries('career', [
                    'IMG_2518.JPG',
                    'IMG_2516.JPG',
                    'IMG_2497.JPG',
                    'IMG_2495.JPG',
                    'IMG_2513.JPG',
                    'IMG_2486.JPG',
                    'IMG_2586.JPG',
                    'IMG_2588.JPG',
                    'IMG_2589.JPG',
                    'IMG_2590.JPG',
                    'IMG_2591.JPG',
                    'IMG_2594.JPG',
                ]),
            ],
            $this->readManifest('career'),
        );
    }

    private function assertGalleryAssets(string $gallery, int $count): void
    {
        $cover = public_path("images/structure/{$gallery}/{$gallery}-cover.webp");
        $this->assertFileExists($cover);
        $this->assertFourByThree($cover);

        foreach (range(1, $count) as $number) {
            $file = sprintf('%s-%02d.webp', $gallery, $number);
            $path = public_path("images/structure/{$gallery}/{$file}");

            $this->assertFileExists($path);
            $this->assertFourByThree($path);
        }
    }

    private function assertFourByThree(string $path): void
    {
        [$width, $height] = getimagesize($path);
        $divisor = $this->greatestCommonDivisor($width, $height);

        $this->assertSame(4, (int) round($width / $divisor));
        $this->assertSame(3, (int) round($height / $divisor));
    }

    /**
     * @param array<int, string> $sources
     * @return array<int, array{source: string, output: string}>
     */
    private function manifestEntries(string $gallery, array $sources): array
    {
        return array_map(
            static fn (string $source, int $index): array => [
                'source' => $source,
                'output' => sprintf('%s-%02d.webp', $gallery, $index + 1),
            ],
            $sources,
            array_keys($sources),
        );
    }

    /**
     * @return array{cover: array{source: string, output: string}, gallery: array<int, array{source: string, output: string}>}
     */
    private function readManifest(string $gallery): array
    {
        $path = public_path("images/structure/{$gallery}/source-manifest.json");

        $this->assertFileExists($path);

        return json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    }

    private function greatestCommonDivisor(int $left, int $right): int
    {
        while ($right !== 0) {
            [$left, $right] = [$right, $left % $right];
        }

        return $left;
    }
}
