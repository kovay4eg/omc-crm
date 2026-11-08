<?php

namespace App\Support;

use App\Models\MediaArchive;
use Illuminate\Support\Facades\Schema;

class MediaUrl
{
    public static function storage(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        $path = MediaStorage::normalizePath($path);

        if ($path === null || ! MediaStorage::isManagedPath($path)) {
            return null;
        }

        $encodedPath = implode(
            '/',
            array_map('rawurlencode', explode('/', $path)),
        );

        return url('media/'.$encodedPath);
    }

    public static function storageVersioned(?string $path): ?string
    {
        $url = self::storage($path);
        $normalizedPath = blank($path) ? null : MediaStorage::normalizePath((string) $path);
        $location = $normalizedPath === null ? null : MediaStorage::find($normalizedPath);

        if ($url === null) {
            return $url;
        }

        if ($location === null) {
            if (! Schema::hasTable('media_archives')) {
                return $url;
            }

            $archive = MediaArchive::query()
                ->where('original_path', $normalizedPath)
                ->whereIn('status', ['verified', 'archived'])
                ->latest('verified_at')
                ->first();

            return $archive?->sha256
                ? $url.'?v='.substr($archive->sha256, 0, 16)
                : $url;
        }

        clearstatcache(true, $location['absolute_path']);
        $modifiedAt = @filemtime($location['absolute_path']);
        $size = @filesize($location['absolute_path']);

        if ($modifiedAt === false || $size === false) {
            return $url;
        }

        return $url.'?v='.dechex($modifiedAt).'-'.dechex($size);
    }
}
