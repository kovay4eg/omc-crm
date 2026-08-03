<?php

use App\Models\SystemLog;

if (! function_exists('system_log')) {
    function system_log(string $action, ?string $description = null): void
    {
        try {
            $isMobileApp = request()->header('X-OMC-App') === 'flutter';

            SystemLog::create([
                'user_id' => auth()->id(),
                'action' => $action,
                'description' => $description,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'source' => $isMobileApp ? 'mobile_app' : 'web_admin',
                'device_name' => $isMobileApp
                    ? rawurldecode((string) request()->header('X-OMC-Device'))
                    : null,
                'platform' => $isMobileApp ? request()->header('X-OMC-Platform') : null,
            ]);
        } catch (Throwable $e) {
        }
    }
}
