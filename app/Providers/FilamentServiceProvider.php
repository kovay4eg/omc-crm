<?php

namespace App\Providers;

use Filament\Facades\Filament;
use Illuminate\Support\ServiceProvider;

class FilamentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Filament::registerRenderHook(
            'panels::body.end',
            fn () => view('filament.components.save-button-watcher')
        );
    }
}
