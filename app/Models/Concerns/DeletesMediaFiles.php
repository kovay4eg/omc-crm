<?php

namespace App\Models\Concerns;

use App\Services\MediaArchiveManager;
use App\Support\MediaImageOptimizer;
use App\Support\MediaStorage;
use Illuminate\Database\Eloquent\Model;
use Throwable;

trait DeletesMediaFiles
{
    protected static function bootDeletesMediaFiles(): void
    {
        static::updated(function (Model $model): void {
            $fields = $model->mediaFields();

            if (! collect($fields)->contains(fn (string $field): bool => $model->isDirty($field))) {
                return;
            }

            $previousPaths = [];
            $currentPaths = [];

            foreach ($fields as $field) {
                $previousPaths = [...$previousPaths, ...MediaStorage::paths($model->getOriginal($field))];
                $currentPaths = [...$currentPaths, ...MediaStorage::paths($model->getAttribute($field))];
            }

            // Один файл може використовуватися в кількох полях (наприклад,
            // афіша і SMM-обкладинка). Не видаляємо його, поки він лишається
            // посиланням хоча б в одному з поточних полів моделі.
            $replacedPaths = array_values(array_diff(
                array_unique($previousPaths),
                array_unique($currentPaths),
            ));

            $model->afterMediaCommit(
                fn () => app(MediaArchiveManager::class)->retire($replacedPaths, 'replaced', $model),
            );
        });

        static::deleted(function (Model $model): void {
            $paths = collect($model->mediaFields())
                ->flatMap(fn (string $field): array => MediaStorage::paths($model->getAttribute($field)))
                ->all();

            $model->afterMediaCommit(
                fn () => app(MediaArchiveManager::class)->retire($paths, 'deleted', $model),
            );
        });

        static::saved(function (Model $model): void {
            $shouldOptimize = collect($model->optimizableMediaFields())->contains(
                fn (string $field): bool => $model->wasRecentlyCreated || $model->wasChanged($field),
            );

            if (! $shouldOptimize) {
                return;
            }

            $model->afterMediaCommit(function () use ($model): void {
                $fresh = $model->newQuery()->find($model->getKey());
                $fresh?->optimizeMediaImages(true);
            });
        });
    }

    /**
     * Converts new raster media to compact WebP files and updates the model
     * only after every replacement has been written successfully.
     *
     * @return array<string, string> old path => new path
     */
    public function optimizeMediaImages(bool $all = false): array
    {
        $fields = array_values(array_filter(
            $this->optimizableMediaFields(),
            fn (string $field): bool => $all || $this->wasRecentlyCreated || $this->wasChanged($field),
        ));

        if ($fields === []) {
            return [];
        }

        $profiles = [];

        foreach ($fields as $field) {
            foreach (MediaStorage::paths($this->getAttribute($field)) as $path) {
                $profiles[$path] = $this->mergeMediaImageProfiles(
                    $profiles[$path] ?? [],
                    $this->mediaImageProfile($field),
                );
            }
        }

        $replacements = [];

        foreach ($profiles as $path => $profile) {
            $optimized = MediaImageOptimizer::optimize($path, $profile);

            if ($optimized !== $path) {
                $replacements[$path] = $optimized;
            }
        }

        if ($replacements === []) {
            return [];
        }

        $previousValues = [];
        $updates = [];

        foreach ($fields as $field) {
            $previousValues[$field] = $this->getAttribute($field);
            $updates[$field] = $this->replaceMediaPaths($previousValues[$field], $replacements);
        }

        try {
            $this->forceFill($updates)->saveQuietly();
        } catch (Throwable $exception) {
            MediaStorage::delete(array_values($replacements));
            $this->forceFill($previousValues);

            throw $exception;
        }

        $currentPaths = MediaStorage::paths(collect($this->mediaFields())
            ->map(fn (string $field): mixed => $this->getAttribute($field))
            ->all());

        app(MediaArchiveManager::class)->retire(
            array_values(array_diff(array_keys($replacements), $currentPaths)),
            'optimized_original',
            $this,
        );

        return $replacements;
    }

    /**
     * @return array<int, string>
     */
    protected function optimizableMediaFields(): array
    {
        return $this->mediaFields();
    }

    /**
     * @return array{max_width?: int, max_height?: int, quality?: int}
     */
    protected function mediaImageProfile(string $field): array
    {
        return [];
    }

    /**
     * When one source is used in multiple fields, keep the strictest dimensions
     * and the highest requested quality so every consumer receives a suitable file.
     *
     * @param  array{max_width?: int, max_height?: int, quality?: int}  $current
     * @param  array{max_width?: int, max_height?: int, quality?: int}  $next
     * @return array{max_width: int, max_height: int, quality: int}
     */
    private function mergeMediaImageProfiles(array $current, array $next): array
    {
        return [
            'max_width' => min($current['max_width'] ?? 2000, $next['max_width'] ?? 2000),
            'max_height' => min($current['max_height'] ?? 2000, $next['max_height'] ?? 2000),
            'quality' => max($current['quality'] ?? 84, $next['quality'] ?? 84),
        ];
    }

    private function afterMediaCommit(callable $callback): void
    {
        $connection = $this->getConnection();

        if ($connection->transactionLevel() > 0) {
            $connection->afterCommit($callback);

            return;
        }

        $callback();
    }

    private function replaceMediaPaths(mixed $value, array $replacements): mixed
    {
        if (is_string($value)) {
            $paths = MediaStorage::paths($value);

            if (count($paths) === 1 && isset($replacements[$paths[0]])) {
                return $replacements[$paths[0]];
            }

            return $value;
        }

        if (! is_array($value)) {
            return $value;
        }

        return array_map(
            fn (mixed $item): mixed => $this->replaceMediaPaths($item, $replacements),
            $value,
        );
    }

    /**
     * @return array<int, string>
     */
    abstract protected function mediaFields(): array;
}
