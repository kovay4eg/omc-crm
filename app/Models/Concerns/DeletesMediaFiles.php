<?php

namespace App\Models\Concerns;

use App\Support\MediaStorage;
use Illuminate\Database\Eloquent\Model;

trait DeletesMediaFiles
{
    protected static function bootDeletesMediaFiles(): void
    {
        static::updating(function (Model $model): void {
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
            MediaStorage::delete(array_values(array_diff(
                array_unique($previousPaths),
                array_unique($currentPaths),
            )));
        });

        static::deleting(function (Model $model): void {
            MediaStorage::deleteModelFiles($model, $model->mediaFields());
        });
    }

    /**
     * @return array<int, string>
     */
    abstract protected function mediaFields(): array;
}
