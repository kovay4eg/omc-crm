<?php

namespace App\Models\Concerns;

use App\Support\MediaStorage;
use Illuminate\Database\Eloquent\Model;

trait DeletesMediaFiles
{
    protected static function bootDeletesMediaFiles(): void
    {
        static::updating(function (Model $model): void {
            MediaStorage::deleteReplacedFiles($model, $model->mediaFields());
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
