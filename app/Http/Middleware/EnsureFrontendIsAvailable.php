<?php

namespace App\Http\Middleware;

use App\Models\HomepageSetting;
use App\Models\SiteSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFrontendIsAvailable
{
    public function handle(Request $request, Closure $next): Response
    {
        $isMaintenanceModeEnabled = (bool) SiteSetting::query()
            ->value('maintenance_mode');

        if (! $isMaintenanceModeEnabled) {
            return $next($request);
        }

        return response()->view('maintenance', [
            'settings' => HomepageSetting::first(),
        ], Response::HTTP_SERVICE_UNAVAILABLE);
    }
}
