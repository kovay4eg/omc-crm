<?php

namespace Tests\Unit;

use App\Support\PartnerLogoOptimizer;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PartnerLogoOptimizerTest extends TestCase
{
    public function test_it_trims_uniform_partner_logo_margins_and_keeps_padding(): void
    {
        Storage::fake('public');
        $path = 'footer-partners/partner.png';
        Storage::disk('public')->makeDirectory('footer-partners');
        $image = imagecreatetruecolor(416, 264);
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        imagefill($image, 0, 0, $white);
        imagefilledrectangle($image, 95, 97, 320, 157, $black);
        imagepng($image, Storage::disk('public')->path($path));
        imagedestroy($image);

        $this->assertTrue(PartnerLogoOptimizer::optimize($path));

        [$width, $height] = getimagesize(Storage::disk('public')->path($path));

        $this->assertSame(246, $width);
        $this->assertSame(81, $height);
        $this->assertFalse(PartnerLogoOptimizer::optimize($path));
    }
}
