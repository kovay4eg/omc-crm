<?php

namespace App\Support;

final class PartnerLogoOptimizer
{
    /**
     * Прибирає однорідні поля навколо логотипу та залишає невеликий безпечний відступ.
     * Файл і його формат не змінюються, тому збережені в БД шляхи лишаються чинними.
     */
    public static function optimize(string $path): bool
    {
        $location = MediaStorage::find($path);

        if ($location === null || ! function_exists('imagecropauto')) {
            return false;
        }

        $source = @file_get_contents($location['absolute_path']);
        $details = $source === false ? false : @getimagesizefromstring($source);

        if ($source === false || $details === false || ! in_array($details[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
            return false;
        }

        $image = @imagecreatefromstring($source);

        if ($image === false) {
            return false;
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $cropped = @imagecropauto($image, IMG_CROP_SIDES);

        if ($cropped === false || (imagesx($cropped) === $width && imagesy($cropped) === $height)) {
            if ($cropped !== false) {
                imagedestroy($cropped);
            }

            imagedestroy($image);

            return false;
        }

        $croppedWidth = imagesx($cropped);
        $croppedHeight = imagesy($cropped);
        $padding = max(8, min(16, (int) ceil(max($croppedWidth, $croppedHeight) * .04)));
        $optimizedWidth = $croppedWidth + ($padding * 2);
        $optimizedHeight = $croppedHeight + ($padding * 2);

        // Повторний запуск не повинен перекодовувати вже оптимізований файл.
        if ($optimizedWidth === $width && $optimizedHeight === $height) {
            imagedestroy($cropped);
            imagedestroy($image);

            return false;
        }

        $optimized = imagecreatetruecolor($optimizedWidth, $optimizedHeight);
        $background = imagecolorsforindex($image, imagecolorat($image, 0, 0));

        imagealphablending($optimized, false);
        imagesavealpha($optimized, true);
        $backgroundColor = imagecolorallocatealpha(
            $optimized,
            $background['red'],
            $background['green'],
            $background['blue'],
            $background['alpha'],
        );
        imagefill($optimized, 0, 0, $backgroundColor);
        imagealphablending($optimized, true);
        imagecopy($optimized, $cropped, $padding, $padding, 0, 0, $croppedWidth, $croppedHeight);

        ob_start();
        $encoded = match ($details[2]) {
            IMAGETYPE_JPEG => imagejpeg($optimized, null, 92),
            IMAGETYPE_PNG => imagepng($optimized, null, 9),
            IMAGETYPE_WEBP => imagewebp($optimized, null, 90),
        };
        $contents = ob_get_clean();

        imagedestroy($optimized);
        imagedestroy($cropped);
        imagedestroy($image);

        if (! $encoded || ! is_string($contents) || $contents === '') {
            return false;
        }

        $permissions = @fileperms($location['absolute_path']);
        $written = @file_put_contents($location['absolute_path'], $contents, LOCK_EX);

        if ($written === false) {
            return false;
        }

        if ($permissions !== false) {
            @chmod($location['absolute_path'], $permissions & 0777);
        }

        return true;
    }
}
