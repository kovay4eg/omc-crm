<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class MediaArchivePressureState
{
    private const CACHE_KEY = 'media-archive:pressure-active';

    public function active(): bool
    {
        return (bool) Cache::get(self::CACHE_KEY, false);
    }

    public function activate(): void
    {
        Cache::forever(self::CACHE_KEY, true);
    }

    public function clear(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Persist the pressure state across scheduler invocations. A run that was
     * triggered below the low threshold remains active until both configured
     * recovery thresholds are met.
     *
     * @param  array{low: bool, recovered: bool}  $snapshot
     */
    public function reconcile(array $snapshot): bool
    {
        if ($snapshot['low']) {
            $this->activate();
        } elseif ($snapshot['recovered']) {
            $this->clear();
        }

        return $this->active();
    }
}
