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
}
