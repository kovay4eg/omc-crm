<?php

use App\Models\SystemLog;

if (!function_exists('system_log')) {
    function system_log(string $action, string $description = null): void
    {
        try {
            SystemLog::create([
                'user_id'     => auth()->id(),
                'action'      => $action,
                'description' => $description,
                'ip'          => request()->ip(),
                'user_agent'  => request()->userAgent(),
            ]);
        } catch (\Throwable $e) {
        }
    }
}