<?php

namespace App\Support;

final class BrandLogo
{
    public const ASSET_PATH = 'images/main-logo-desktop-svg.svg';

    /**
     * Previously bundled defaults that were uploaded into the media library.
     * They are not genuine vector assets and must not override the canonical logo.
     */
    private const LEGACY_STORAGE_PATHS = [
        'homepage/Logo_1 1.svg',
    ];

    public static function url(): string
    {
        $absolutePath = public_path(self::ASSET_PATH);
        $hash = is_file($absolutePath) ? hash_file('sha256', $absolutePath) : false;

        return asset(self::ASSET_PATH).($hash ? '?v='.substr($hash, 0, 12) : '');
    }

    public static function resolve(?string $storedPath): string
    {
        $normalizedPath = rawurldecode(str_replace('\\', '/', ltrim(trim((string) $storedPath), '/')));

        if ($normalizedPath === '' || in_array($normalizedPath, self::LEGACY_STORAGE_PATHS, true)) {
            return self::url();
        }

        return MediaUrl::storage($storedPath) ?? self::url();
    }
}
