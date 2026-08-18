<?php

namespace App\Http\Controllers;

use App\Models\MobilePushDevice;
use App\Models\SystemLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminProWebPushController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        abort_unless($request->user()?->canAccessAdminProMail(), 403, 'Немає доступу до поштових сповіщень.');
        $data = $request->validate([
            'token' => ['required', 'string', 'min:20', 'max:4096'],
        ]);
        $tokenHash = hash('sha256', $data['token']);
        $device = MobilePushDevice::query()->updateOrCreate(
            ['token_hash' => $tokenHash],
            [
                'user_id' => $request->user()->getKey(),
                'token' => $data['token'],
                'platform' => 'web',
                'device_name' => str($request->userAgent())->limit(120)->toString(),
                'preferences' => ['push_mail' => true],
                'last_seen_at' => now(),
            ],
        );

        SystemLog::query()->create([
            'user_id' => $request->user()->getKey(),
            'action' => $device->wasRecentlyCreated ? 'web_push_registered' : 'web_push_refreshed',
            'description' => 'Увімкнено браузерні сповіщення про пошту',
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'source' => 'web_admin',
            'platform' => 'web',
        ]);

        return response()->json(['message' => 'Браузерні сповіщення увімкнено.']);
    }
}
