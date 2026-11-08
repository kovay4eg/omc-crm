<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

final class MediaImageOptimizer
{
    /**
     * Converts a managed JPEG/PNG image to a resized WebP copy.
     *
     * The source is deliberately kept until its model has successfully saved
     * the returned path. Unsupported files and already-small WebP images are
     * returned unchanged.
     *
     * @param  array{max_width?: int, max_height?: int, quality?: int}  $profile
     */
    public static function optimize(string $path, array $profile = []): string
    {
        $location = MediaStorage::find($path);

        if ($location === null || ! function_exists('imagewebp')) {
            return $path;
        }

        $details = @getimagesize($location['absolute_path']);
        $type = $details[2] ?? null;

        if (! in_array($type, [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
            return $path;
        }

        $maxWidth = max(1, (int) ($profile['max_width'] ?? 2000));
        $maxHeight = max(1, (int) ($profile['max_height'] ?? 2000));
        $quality = max(1, min(100, (int) ($profile['quality'] ?? 84)));
        $sourceWidth = (int) $details[0];
        $sourceHeight = (int) $details[1];

        if (
            $type === IMAGETYPE_WEBP
            && $sourceWidth <= $maxWidth
            && $sourceHeight <= $maxHeight
        ) {
            return $path;
        }

        $source = null;
        $target = null;

        try {
            $source = self::load($location['absolute_path'], $type);

            if (! $source) {
                return $path;
            }

            if ($type === IMAGETYPE_JPEG) {
                $source = self::orientJpeg($source, $location['absolute_path']);
            }

            $sourceWidth = imagesx($source);
            $sourceHeight = imagesy($source);
            $scale = min(1, $maxWidth / $sourceWidth, $maxHeight / $sourceHeight);
            $targetWidth = max(1, (int) round($sourceWidth * $scale));
            $targetHeight = max(1, (int) round($sourceHeight * $scale));
            $target = imagecreatetruecolor($targetWidth, $targetHeight);

            if (! $target) {
                return $path;
            }

            imagealphablending($target, false);
            imagesavealpha($target, true);
            imagefill($target, 0, 0, imagecolorallocatealpha($target, 0, 0, 0, 127));
            imagecopyresampled(
                $target,
                $source,
                0,
                0,
                0,
                0,
                $targetWidth,
                $targetHeight,
                $sourceWidth,
                $sourceHeight,
            );

            ob_start();
            $encoded = imagewebp($target, null, $quality) ? ob_get_clean() : false;

            if (! is_string($encoded) || $encoded === '') {
                if (ob_get_level() > 0) {
                    ob_end_clean();
                }

                return $path;
            }

            $encodedDetails = @getimagesizefromstring($encoded);

            if (($encodedDetails[2] ?? null) !== IMAGETYPE_WEBP) {
                return $path;
            }

            $newPath = self::webpPath($location['path'], $encoded);
            $storage = Storage::disk($location['disk']);

            if (! $storage->exists($newPath)) {
                $options = $location['disk'] === 'public' ? ['visibility' => 'public'] : [];

                if (! $storage->put($newPath, $encoded, $options)) {
                    return $path;
                }
            }

            return $newPath;
        } catch (Throwable $exception) {
            Log::warning('Image optimization failed; the original file was preserved.', [
                'path' => $path,
                'error' => $exception->getMessage(),
            ]);

            return $path;
        } finally {
            if ($source instanceof \GdImage) {
                imagedestroy($source);
            }

            if ($target instanceof \GdImage) {
                imagedestroy($target);
            }
        }
    }

    private static function load(string $absolutePath, int $type): \GdImage|false
    {
        return match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($absolutePath),
            IMAGETYPE_PNG => @imagecreatefrompng($absolutePath),
            IMAGETYPE_WEBP => @imagecreatefromwebp($absolutePath),
            default => false,
        };
    }

    private static function orientJpeg(\GdImage $image, string $absolutePath): \GdImage
    {
        if (! function_exists('exif_read_data')) {
            return $image;
        }

        $orientation = (int) ((@exif_read_data($absolutePath)['Orientation'] ?? 1));

        if (in_array($orientation, [2, 4, 5, 7], true)) {
            imageflip($image, in_array($orientation, [2, 5], true) ? IMG_FLIP_HORIZONTAL : IMG_FLIP_VERTICAL);
        }

        $degrees = match ($orientation) {
            3 => 180,
            5, 6 => -90,
            7, 8 => 90,
            default => 0,
        };

        if ($degrees === 0) {
            return $image;
        }

        $rotated = imagerotate($image, $degrees, 0);

        if (! $rotated) {
            return $image;
        }

        imagedestroy($image);

        return $rotated;
    }

    private static function webpPath(string $path, string $contents): string
    {
        $directory = pathinfo($path, PATHINFO_DIRNAME);
        $basename = pathinfo($path, PATHINFO_FILENAME);
        $filename = $basename.'-'.substr(hash('sha256', $contents), 0, 12).'.webp';

        return $directory === '.' ? $filename : $directory.'/'.$filename;
    }
}
