<?php

namespace App\Console\Commands;

use App\Models\AppAnnouncement;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Event;
use App\Models\EventSummary;
use App\Models\EventSummaryImage;
use App\Models\HomepageSetting;
use App\Models\SiteSetting;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;

class OptimizeMediaImages extends Command
{
    protected $signature = 'media:optimize-images
        {--model=* : Limit the run to one or more model aliases}
        {--force : Allow changing files in production}';

    protected $description = 'Convert managed raster photos to resized WebP files';

    /** @var array<string, class-string<Model>> */
    private const MODELS = [
        'employees' => Employee::class,
        'departments' => Department::class,
        'team-banner' => SiteSetting::class,
        'homepage' => HomepageSetting::class,
        'events' => Event::class,
        'event-summaries' => EventSummary::class,
        'event-summary-images' => EventSummaryImage::class,
        'app-announcements' => AppAnnouncement::class,
    ];

    public function handle(): int
    {
        if (app()->isProduction() && ! $this->option('force')) {
            $this->error('In production, rerun with --force after creating a backup.');

            return self::FAILURE;
        }

        $requested = array_values(array_filter((array) $this->option('model')));
        $aliases = $requested === [] ? array_keys(self::MODELS) : $requested;
        $unknown = array_values(array_diff($aliases, array_keys(self::MODELS)));

        if ($unknown !== []) {
            $this->error('Unknown model alias: '.implode(', ', $unknown));
            $this->line('Available aliases: '.implode(', ', array_keys(self::MODELS)));

            return self::INVALID;
        }

        $converted = 0;

        foreach ($aliases as $alias) {
            $modelClass = self::MODELS[$alias];
            $modelCount = 0;

            $modelClass::query()->chunkById(100, function ($models) use (&$converted, &$modelCount): void {
                foreach ($models as $model) {
                    $replacements = $model->optimizeMediaImages(true);
                    $modelCount += count($replacements);
                    $converted += count($replacements);
                }
            });

            $this->line(sprintf('%s: %d converted', $alias, $modelCount));
        }

        $this->info(sprintf('Done. %d image(s) converted to WebP.', $converted));

        return self::SUCCESS;
    }
}
