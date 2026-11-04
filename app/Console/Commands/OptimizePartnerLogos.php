<?php

namespace App\Console\Commands;

use App\Models\FooterSetting;
use App\Support\MediaStorage;
use App\Support\PartnerLogoOptimizer;
use Illuminate\Console\Command;

class OptimizePartnerLogos extends Command
{
    protected $signature = 'footer:optimize-partner-logos';

    protected $description = 'Прибирає зайві поля навколо логотипів партнерів у футері';

    public function handle(): int
    {
        $paths = MediaStorage::paths(FooterSetting::query()->first()?->partner_logos);
        $optimized = 0;

        foreach ($paths as $path) {
            if (PartnerLogoOptimizer::optimize($path)) {
                $optimized++;
                $this->line('Оптимізовано: '.$path);
            }
        }

        $this->info(sprintf('Готово. Оптимізовано %d із %d логотипів.', $optimized, count($paths)));

        return self::SUCCESS;
    }
}
