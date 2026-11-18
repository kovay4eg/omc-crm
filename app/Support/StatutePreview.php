<?php

namespace App\Support;

final class StatutePreview
{
    public const SOURCE_PATH = 'statutes/statut-701f31e4c1f3143a.pdf';

    public const SOURCE_SHA256 = '701F31E4C1F3143AD9D406B49CEACB978D9A7D3287D4E2E6CCB7FA55C2E173C4';

    public const PREVIEW_PATH = 'documents/statute-701f31e4c1f3143a-a4-v1.pdf';

    public const PREVIEW_SHA256 = 'DA56599E122D807063A842E599FD45CF60A9326227326BD61313951A65D8D1CF';

    public static function url(?string $sourcePath): ?string
    {
        if (blank($sourcePath)) {
            return null;
        }

        $normalizedPath = MediaStorage::normalizePath((string) $sourcePath);

        if ($normalizedPath !== self::SOURCE_PATH) {
            return null;
        }

        if (! is_file(public_path(self::PREVIEW_PATH))) {
            return null;
        }

        return asset(self::PREVIEW_PATH).'?v='.substr(self::PREVIEW_SHA256, 0, 16);
    }
}
