<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command('admin-pro-mail:check')
    ->everyMinute()
    ->withoutOverlapping(5);

Schedule::command('google:sync-registrations')
    ->everyTenMinutes()
    ->withoutOverlapping(15)
    ->runInBackground();

Schedule::command('media:archive-if-low')
    ->everyFifteenMinutes()
    ->withoutOverlapping(120)
    ->onOneServer()
    ->runInBackground();

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
