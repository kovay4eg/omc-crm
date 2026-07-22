<?php

namespace App\Console\Commands;

use App\Models\CalendarPlan;
use App\Models\Employee;
use App\Models\Event;
use App\Models\EventSummary;
use App\Models\EventSummaryImage;
use App\Models\FooterSetting;
use App\Models\HomepageSetting;
use App\Models\Report;
use App\Models\SiteSetting;
use App\Models\Statute;
use App\Support\MediaStorage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PruneOrphanedMedia extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:prune-orphans
                            {--force : Видалити знайдені файли після перевірки списку}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Знаходить невикористані медіафайли, не торкаючись тимчасових файлів Livewire';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $referenced = array_fill_keys($this->referencedMediaPaths(), true);
        $orphans = [];

        foreach ($this->storageRoots() as $root) {
            foreach (MediaStorage::managedDirectories() as $directory) {
                $sourceDirectory = $root['prefix'].$directory;

                foreach (Storage::disk($root['disk'])->allFiles($sourceDirectory) as $file) {
                    $relativePath = str_starts_with($file, $root['prefix'])
                        ? substr($file, strlen($root['prefix']))
                        : $file;

                    if (isset($referenced[$relativePath])) {
                        continue;
                    }

                    $orphans[] = [
                        'disk' => $root['disk'],
                        'file' => $file,
                        'size' => Storage::disk($root['disk'])->size($file),
                    ];
                }
            }
        }

        if ($orphans === []) {
            $this->info('Невикористаних медіафайлів не знайдено.');

            return self::SUCCESS;
        }

        $totalSize = array_sum(array_column($orphans, 'size'));

        $this->table(
            ['Диск', 'Файл', 'Розмір'],
            array_map(
                fn (array $file): array => [$file['disk'], $file['file'], $this->formatBytes($file['size'])],
                array_slice($orphans, 0, 30),
            ),
        );

        $this->warn(sprintf(
            'Знайдено %d файлів на %s.',
            count($orphans),
            $this->formatBytes($totalSize),
        ));

        if (! $this->option('force')) {
            $this->line('Нічого не видалено. Після перевірки запусти: php artisan media:prune-orphans --force');

            return self::SUCCESS;
        }

        foreach ($orphans as $file) {
            Storage::disk($file['disk'])->delete($file['file']);
        }

        $this->info('Невикористані медіафайли видалено.');

        return self::SUCCESS;
    }

    /** @return array<int, string> */
    private function referencedMediaPaths(): array
    {
        $values = [
            Employee::query()->pluck('photo')->all(),
            Event::query()->pluck('image')->all(),
            Event::query()->pluck('smm_image')->all(),
            EventSummary::query()->pluck('smm_image')->all(),
            EventSummaryImage::query()->pluck('image')->all(),
            HomepageSetting::query()->pluck('banner_image')->all(),
            HomepageSetting::query()->pluck('mobile_banner_image')->all(),
            HomepageSetting::query()->pluck('logo')->all(),
            HomepageSetting::query()->pluck('smm_image')->all(),
            SiteSetting::query()->pluck('team_banner')->all(),
            FooterSetting::query()->pluck('partner_logos')->all(),
            CalendarPlan::query()->pluck('file')->all(),
            Report::query()->pluck('file')->all(),
            Statute::query()->pluck('file')->all(),
        ];

        $paths = [];

        foreach ($values as $value) {
            $paths = [...$paths, ...MediaStorage::paths($value)];
        }

        return array_values(array_unique($paths));
    }

    /** @return array<int, array{disk: string, prefix: string}> */
    private function storageRoots(): array
    {
        return [
            ['disk' => 'public', 'prefix' => ''],
            ['disk' => 'local', 'prefix' => ''],
            ['disk' => 'local', 'prefix' => 'private/'],
        ];
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['Б', 'КБ', 'МБ', 'ГБ'];
        $power = $bytes > 0 ? min((int) floor(log($bytes, 1024)), count($units) - 1) : 0;

        return number_format($bytes / (1024 ** $power), $power === 0 ? 0 : 1, ',', ' ').' '.$units[$power];
    }
}
