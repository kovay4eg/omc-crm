<?php

namespace App\Providers;

use App\Models\SystemLog;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [];

    public function boot(): void
    {
        parent::boot();

        // ❌ НЕВДАЛИЙ ЛОГІН
        \Event::listen(Failed::class, function ($event) {
            SystemLog::create([
                'user_id' => null,
                'action' => 'login_failed',
                'description' => 'Невдала спроба входу: '.($event->credentials['email'] ?? 'unknown'),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });

        // ✅ УСПІШНИЙ ЛОГІН
        \Event::listen(Login::class, function ($event) {
            SystemLog::create([
                'user_id' => $event->user->id,
                'action' => 'login_success',
                'description' => 'Успішний вхід: '.$event->user->email,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });

        // 🚪 LOGOUT
        \Event::listen(Logout::class, function ($event) {
            SystemLog::create([
                'user_id' => $event->user?->id,
                'action' => 'logout',
                'description' => 'Вихід з системи',
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });
    }
}
