<?php

namespace App\Console\Commands;

use App\Contracts\CloudArchiveClient;
use App\Models\SystemLog;
use App\Services\DiskSpaceMonitor;
use App\Services\MediaArchiveCandidateService;
use App\Services\MediaArchiveManager;
use App\Services\MediaArchivePressureState;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Throwable;

class ArchiveMediaIfLow extends Command
{
    protected $signature = 'media:archive-if-low
                            {--force : Запустити архівацію незалежно від порога диска}
                            {--dry-run : Лише показати кандидатів без завантаження та видалення}
                            {--limit= : Максимальна кількість файлів за один запуск}';

    protected $description = 'Безпечно переносить старі медіа в OMC Cloud, коли на диску мало місця';

    public function __construct(
        private readonly DiskSpaceMonitor $disk,
        private readonly MediaArchiveCandidateService $candidates,
        private readonly MediaArchiveManager $archives,
        private readonly MediaArchivePressureState $pressure,
        private readonly CloudArchiveClient $cloud,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $enabled = (bool) config('media_archive.enabled', false);
        $limit = $this->option('limit') !== null
            ? max(1, (int) $this->option('limit'))
            : max(1, (int) config('media_archive.batch_size', 200));

        if (! $enabled && ! $dryRun) {
            $this->line('Архівацію вимкнено (OMC_MEDIA_ARCHIVE_ENABLED=false).');

            return self::SUCCESS;
        }

        try {
            $before = $this->disk->snapshot();
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());
            $this->systemLog('media_archive_failed', ['stage' => 'disk_check', 'error' => $exception->getMessage()], true);

            return self::FAILURE;
        }

        $this->line(sprintf(
            'Вільно %s з %s (%s%%).',
            $this->formatBytes($before['free']),
            $this->formatBytes($before['total']),
            number_format($before['free_percent'], 2, ',', ' '),
        ));

        $pressureActive = $dryRun
            ? $this->pressure->active()
            : $this->pressure->reconcile($before);

        if (! $pressureActive && ! $this->option('force') && ! $dryRun) {
            $this->info('Вільного місця достатньо; архівація не потрібна.');

            return self::SUCCESS;
        }

        $candidates = $this->candidates->candidates($limit);

        if ($candidates === []) {
            $this->info('Файлів, придатних до архівації, не знайдено.');

            return self::SUCCESS;
        }

        $this->table(
            ['Причина', 'Файл', 'Розмір'],
            array_map(fn (array $candidate): array => [
                $candidate['reason'],
                $candidate['path'],
                $this->formatBytes($candidate['size']),
            ], array_slice($candidates, 0, 30)),
        );

        $planned = array_sum(array_column($candidates, 'size'));
        $this->line(sprintf('Кандидатів: %d, потенційно %s.', count($candidates), $this->formatBytes($planned)));

        if ($dryRun) {
            $this->info('Dry-run завершено: файли не завантажувались і не видалялись.');

            return self::SUCCESS;
        }

        if (! $this->cloud->configured()) {
            $this->error('OMC Cloud не налаштовано. Локальні файли залишено без змін.');
            $this->systemLog('media_archive_failed', ['stage' => 'configuration'], true);

            return self::FAILURE;
        }

        $lock = Cache::lock('media-archive:disk-monitor', 3600);

        if (! $lock->get()) {
            $this->line('Інший процес архівації вже працює.');

            return self::SUCCESS;
        }

        $archived = 0;
        $failed = 0;
        $released = 0;

        try {
            foreach ($candidates as $candidate) {
                try {
                    $bytes = $this->archives->archiveCandidate($candidate);
                    $released += $bytes;
                    $archived++;
                    $this->line(sprintf('Архівовано: %s (%s)', $candidate['path'], $this->formatBytes($bytes)));
                } catch (Throwable $exception) {
                    $failed++;
                    $this->warn(sprintf('Не вдалося архівувати %s: %s', $candidate['path'], $exception->getMessage()));
                }

                if (! $this->option('force')) {
                    $current = $this->disk->snapshot();

                    // Keep the pressure flag between scheduler invocations;
                    // clear it only after both recovery thresholds are met.
                    if (! $this->pressure->reconcile($current)) {
                        break;
                    }
                }
            }
        } finally {
            $lock->release();
        }

        $after = $this->disk->snapshot();

        if (! $this->option('force')) {
            $this->pressure->reconcile($after);
        }
        $payload = [
            'archived' => $archived,
            'failed' => $failed,
            'released_bytes' => $released,
            'free_before' => $before['free'],
            'free_after' => $after['free'],
        ];

        $this->systemLog(
            $failed > 0 ? 'media_archive_failed' : 'media_archive_completed',
            $payload,
            $failed > 0,
        );

        $this->info(sprintf(
            'Завершено: %d архівовано, %d помилок, звільнено %s.',
            $archived,
            $failed,
            $this->formatBytes($released),
        ));

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    /** @param array<string, mixed> $payload */
    private function systemLog(string $action, array $payload, bool $throttle = false): void
    {
        if (! Schema::hasTable('system_logs')) {
            return;
        }

        if ($throttle && ! Cache::add('system-log:'.$action.':'.now()->format('YmdH'), true, 3600)) {
            return;
        }

        try {
            SystemLog::query()->create([
                'user_id' => null,
                'action' => $action,
                'description' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'ip' => null,
                'user_agent' => 'Laravel Scheduler',
                'source' => 'system',
                'device_name' => null,
                'platform' => PHP_OS_FAMILY,
            ]);
        } catch (Throwable) {
            // Logging must not make a verified archival run destructive/fail.
        }
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['Б', 'КБ', 'МБ', 'ГБ', 'ТБ'];
        $power = $bytes > 0 ? min((int) floor(log($bytes, 1024)), count($units) - 1) : 0;

        return number_format($bytes / (1024 ** $power), $power === 0 ? 0 : 1, ',', ' ').' '.$units[$power];
    }
}
