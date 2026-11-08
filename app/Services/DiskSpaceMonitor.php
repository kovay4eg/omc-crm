<?php

namespace App\Services;

use RuntimeException;

class DiskSpaceMonitor
{
    /**
     * @return array{path: string, total: int, free: int, used: int, free_percent: float, low: bool, recovered: bool}
     */
    public function snapshot(?string $path = null): array
    {
        $path ??= storage_path('app');
        $total = @disk_total_space($path);
        $free = @disk_free_space($path);

        if ($total === false || $free === false || $total <= 0) {
            throw new RuntimeException('Не вдалося визначити вільне місце у сховищі сайту.');
        }

        return [
            'path' => $path,
            ...$this->assess((int) $total, (int) $free),
        ];
    }

    /**
     * Pure threshold calculation kept public so it can be tested without
     * depending on the filesystem of the machine running the test suite.
     *
     * @return array{total: int, free: int, used: int, free_percent: float, low: bool, recovered: bool}
     */
    public function assess(int $total, int $free): array
    {
        if ($total <= 0 || $free < 0 || $free > $total) {
            throw new RuntimeException('Некоректні дані про місце у сховищі.');
        }

        $freePercent = round(($free / $total) * 100, 2);
        $triggerPercent = (float) config('media_archive.thresholds.trigger_percent', 10);
        $recoveryPercent = (float) config('media_archive.thresholds.recovery_percent', 15);
        $triggerBytes = max(0, (int) config('media_archive.thresholds.trigger_bytes', 2 * 1024 ** 3));
        $recoveryBytes = max($triggerBytes, (int) config('media_archive.thresholds.recovery_bytes', 4 * 1024 ** 3));

        return [
            'total' => $total,
            'free' => $free,
            'used' => $total - $free,
            'free_percent' => $freePercent,
            // Either a low percentage or a low absolute amount is enough to
            // start archiving. Both recovery conditions must be met to stop.
            'low' => $freePercent <= $triggerPercent || $free <= $triggerBytes,
            'recovered' => $freePercent >= $recoveryPercent && $free >= $recoveryBytes,
        ];
    }
}
