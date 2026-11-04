<?php

namespace App\Support;

class MediaUrl
{
    public static function storage(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        $encodedPath = implode(
            '/',
            array_map('rawurlencode', explode('/', ltrim($path, '/'))),
        );

        return asset('storage/'.$encodedPath);
    }

    public static function storageVersioned(?string $path): ?string
    {
        $url = self::storage($path);
        $location = blank($path) ? null : MediaStorage::find($path);

        if ($url === null || $location === null) {
            return $url;
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
