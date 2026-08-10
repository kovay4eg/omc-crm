<?php

declare(strict_types=1);

use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QROutputInterface;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

require dirname(__DIR__).'/vendor/autoload.php';

$platform = $argv[1] ?? null;
$urls = [
    'android' => 'https://omc.pl.ua/download/android?abi=arm64-v8a',
    'ios' => 'https://omc.pl.ua/download/ios',
];

if (! isset($urls[$platform])) {
    fwrite(STDERR, "Usage: php tools/generate_platform_qr.php android|ios\n");
    exit(1);
}

$outputDirectory = dirname(__DIR__).'/public/images/qr';
$prefix = "omc-{$platform}-download-qr";

$pngOptions = new QROptions([
    'outputType' => QROutputInterface::GDIMAGE_PNG,
    'outputBase64' => false,
    'eccLevel' => EccLevel::H,
    'scale' => 20,
    'addQuietzone' => true,
    'quietzoneSize' => 4,
]);

(new QRCode($pngOptions))->render($urls[$platform], "{$outputDirectory}/{$prefix}-square.png");

$svgOptions = new QROptions([
    'outputType' => QROutputInterface::MARKUP_SVG,
    'outputBase64' => false,
    'eccLevel' => EccLevel::H,
    'scale' => 20,
    'addQuietzone' => true,
    'quietzoneSize' => 4,
]);

(new QRCode($svgOptions))->render($urls[$platform], "{$outputDirectory}/{$prefix}.svg");

fwrite(STDOUT, "Generated {$platform} QR for {$urls[$platform]}\n");
