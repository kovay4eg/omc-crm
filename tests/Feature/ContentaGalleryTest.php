<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentaGalleryTest extends TestCase
{
    use RefreshDatabase;

    public function test_contenta_card_and_gallery_use_the_supplied_local_photos(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('images/structure/contenta/contenta-cover.webp', false);
        $response->assertSee('data-contenta-carousel', false);
        $this->assertSame(
            10,
            substr_count($response->getContent(), 'class="contenta-carousel__slide"'),
        );

        foreach (range(1, 10) as $number) {
            $file = sprintf('contenta-%02d.webp', $number);

            $response->assertSee("images/structure/contenta/{$file}", false);
            $this->assertFileExists(public_path("images/structure/contenta/{$file}"));

            [$width, $height] = getimagesize(public_path("images/structure/contenta/{$file}"));
            $divisor = $this->greatestCommonDivisor($width, $height);
            $this->assertSame(4, (int) round($width / $divisor));
            $this->assertSame(3, (int) round($height / $divisor));
        }

        $this->assertFileExists(public_path('images/structure/contenta/contenta-cover.webp'));
    }

    private function greatestCommonDivisor(int $left, int $right): int
    {
        while ($right !== 0) {
            [$left, $right] = [$right, $left % $right];
        }

        return $left;
    }
}
