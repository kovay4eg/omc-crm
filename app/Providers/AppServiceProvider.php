<?php

namespace App\Providers;

use App\Models\Event;
use App\Observers\EventObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Event::observe(EventObserver::class);

        RateLimiter::for('crm-login', function (Request $request): array {
            $email = Str::lower(trim((string) $request->input('email')));
            $ip = $request->ip() ?? 'unknown';

            return [
                Limit::perMinute(5)->by($email.'|'.$ip),
                Limit::perHour(30)->by($ip),
            ];
        });

        RateLimiter::for('crm-api', function (Request $request): Limit {
            $identity = $request->user()?->getAuthIdentifier() ?? $request->ip() ?? 'unknown';

            return Limit::perMinute(180)->by((string) $identity);
        });
    }
}
