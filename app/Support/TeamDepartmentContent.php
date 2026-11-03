<?php

namespace App\Support;

use App\Models\Department;

final class TeamDepartmentContent
{
    public static function photoId(string $path): string
    {
        return hash('sha256', $path);
    }

    public static function revision(Department $department): string
    {
        return hash('sha256', json_encode([
            'name' => (string) $department->name,
            'description' => $department->description,
            'photo' => $department->photo,
            'slider_enabled' => (bool) $department->slider_enabled,
            'slider_photos' => array_values($department->slider_photos ?? []),
            'updated_at' => $department->updated_at?->toISOString(),
        ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
    }
}
