<?php

namespace App\Http\Middleware;

use App\Models\SiteVisit;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class TrackSiteVisit
{
    /**
     * Записує лише анонімну активність відвідувача та не впливає на доступність сайту.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $request->isMethod('GET') || $request->expectsJson() || ! $response->isSuccessful() || ! $request->hasSession()) {
            return $response;
        }

        try {
            $sessionId = $request->session()->getId();

            if (! $sessionId) {
                return $response;
            }

            // Не зберігаємо IP-адреси, імена чи інші персональні дані.
            $sessionHash = hash('sha256', $sessionId);

            if (Cache::add('site-visit:' . $sessionHash, true, now()->addMinute())) {
                $now = now('Europe/Kyiv');

                SiteVisit::query()->updateOrCreate(
                    [
                        'session_hash' => $sessionHash,
                        'visited_on' => $now->toDateString(),
                    ],
                    ['last_seen_at' => $now],
                );
            }
        } catch (Throwable) {
            // Статистика не повинна блокувати роботу публічного сайту.
        }

        return $response;
    }
}
