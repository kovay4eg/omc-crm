<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckComposer extends Command
{
    protected $signature = 'check:composer';

    protected $description = 'Check if Composer is available';

    public function handle()
    {
        exec('composer -V 2>&1', $output, $code);

        if ($code !== 0) {
            $this->error('Composer НЕ знайдено');
            $this->line(implode("\n", $output));

            return 1;
        }

        $this->info('Composer працює:');
        $this->line(implode("\n", $output));

        return 0;
    }
}
