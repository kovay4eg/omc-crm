<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Event;
use App\Observers\EventObserver;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Event::observe(EventObserver::class);
    }
}