<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class MediaStorage
{
    /**
     * Папки, якими керує адмінка. Тимчасові файли Livewire навмисно не входять.
     *
     * @return array<int, string>
     */
    public static function managedDirectories(): array
    {
        return [
            'homepage',
            'employees',
            'events',
            'team',
            'smm',
            'event-summaries',
            'footer-partners',
            'calendar-plans',
            'reports',
            'statutes',
        ];
    }

    /**
     * Перетворює значення з поля, JSON або масиву у перелік відносних шляхів.
     *
     * @return array<int, string>
     */
    public static function paths(mixed $value): array
    {
        if (blank($value)) {
            return [];
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return self::paths($decoded);
            }

            $path = self::normalizePath($value);

            return $path === null ? [] : [$path];
        }

        if (! is_iterable($value)) {
            return [];
        }

        $paths = [];

        foreach ($value as $item) {
            $paths = [...$paths, ...self::paths($item)];
        }

        return array_values(array_unique($paths));
    }

    /**
     * Видаляє файл з усіх можливих старих і нових місць зберігання.
     */
    public static function delete(mixed $value): void
    {
        foreach (self::paths($value) as $path) {
            foreach (self::locations($path) as $location) {
                $storage = Storage::disk($location['disk']);

                if ($storage->exists($location['path'])) {
                    $storage->delete($location['path']);
                }
            }
        }
    }

    /**
     * Під час оновлення моделі прибирає лише файли, яких більше немає в її полях.
     *
     * @param  array<int, string>  $fields
     */
    public static function deleteReplacedFiles(Model $model, array $fields): void
    {
        foreach ($fields as $field) {
            if (! $model->isDirty($field)) {
                continue;
            }

            $previous = self::paths($model->getOriginal($field));
            $current = self::paths($model->getAttribute($field));

            self::delete(array_values(array_diff($previous, $current)));
        }
    }

    /**
     * @param  array<int, string>  $fields
     */
    public static function deleteModelFiles(Model $model, array $fields): void
    {
        foreach ($fields as $field) {
            self::delete($model->getAttribute($field));
        }
    }

    /**
     * Дані для FileUpload. Старі файли з приватного сховища відкриваються через
     * контрольований маршрут, тому після перезавантаження їх також видно в адмінці.
     *
     * @param  string|array<string>|null  $storedFileNames
     * @return array{name: string, size: int, type: string|null, url: string}|null
     */
    public static function uploadedFileDetails(string $path, string|array|null $storedFileNames, ?string $legacyUrl = null): ?array
    {
        $location = self::find($path);

        if ($location === null) {
            return null;
        }

        $storage = Storage::disk($location['disk']);
        $name = is_array($storedFileNames)
            ? ($storedFileNames[$path] ?? basename($path))
            : ($storedFileNames ?: basename($path));

        return [
            'name' => $name,
            'size' => $storage->size($location['path']),
            'type' => $storage->mimeType($location['path']),
            // Для вже збережених записів використовуємо контрольований маршрут.
            // Він працює і для старих, і для нових шляхів зберігання та не
            // залежить від символічного посилання public/storage на сервері.
            'url' => $legacyUrl ?: $storage->url($location['path']),
        ];
    }

    public static function response(string $path): BinaryFileResponse
    {
        $location = self::find($path);

        abort_if($location === null, 404);

        return response()->file($location['absolute_path'], [
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    /**
     * @return array{disk: string, path: string, absolute_path: string}|null
     */
    public static function find(string $path): ?array
    {
        foreach (self::locations($path) as $location) {
            $storage = Storage::disk($location['disk']);

            if (! $storage->exists($location['path'])) {
                continue;
            }

            return [
                ...$location,
                'absolute_path' => $storage->path($location['path']),
            ];
        }

        return null;
    }

    /**
     * @return array<int, array{disk: string, path: string}>
     */
    public static function locations(string $path): array
    {
        $path = self::normalizePath($path);

        if ($path === null) {
            return [];
        }

        return [
            ['disk' => 'public', 'path' => $path],
            ['disk' => 'local', 'path' => $path],
            ['disk' => 'local', 'path' => 'private/'.$path],
        ];
    }

    private static function normalizePath(string $path): ?string
    {
        $path = str_replace('\\', '/', ltrim(trim($path), '/\\'));

        if (str_starts_with($path, 'private/')) {
            $path = substr($path, strlen('private/'));
        }

        if ($path === '' || str_contains($path, '..')) {
            return null;
        }

        return $path;
    }
}
