<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\MobilePushDevice;
use App\Models\SystemLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MobilePushDeviceController extends Controller
{
    private const PREFERENCE_KEYS = [
        'push_registrations',
        'push_places',
        'push_events',
        'push_news',
        'push_system',
    ];

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'min:20', 'max:4096'],
            'platform' => ['required', Rule::in(['android', 'ios'])],
            'device_name' => ['nullable', 'string', 'max:120'],
            'preferences' => ['nullable', 'array'],
            ...collect(self::PREFERENCE_KEYS)
                ->mapWithKeys(fn (string $key): array => ['preferences.'.$key => ['sometimes', 'boolean']])
                ->all(),
        ]);

        $tokenHash = hash('sha256', $data['token']);
        $preferences = collect($data['preferences'] ?? [])
            ->only(self::PREFERENCE_KEYS)
            ->map(fn (mixed $value): bool => (bool) $value)
            ->all();

        $device = MobilePushDevice::query()->updateOrCreate(
            ['token_hash' => $tokenHash],
            [
                'user_id' => $request->user()->id,
                'token' => $data['token'],
                'platform' => $data['platform'],
                'device_name' => $data['device_name'] ?? null,
                'preferences' => $preferences,
                'last_seen_at' => now(),
            ],
        );

        SystemLog::query()->create([
            'user_id' => $request->user()->id,
            'action' => $device->wasRecentlyCreated ? 'push_device_registered' : 'push_device_updated',
            'description' => 'Оновлено налаштування push-пристрою',
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'source' => 'mobile_app',
            'device_name' => $data['device_name'] ?? null,
            'platform' => $data['platform'],
        ]);

        return response()->json([
            'message' => 'Push-пристрій зареєстровано.',
            'data' => [
                'id' => $device->id,
                'platform' => $device->platform,
                'preferences' => $device->preferences,
                'last_seen_at' => $device->last_seen_at?->toIso8601String(),
            ],
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'min:20', 'max:4096'],
        ]);

        $deleted = MobilePushDevice::query()
            ->where('user_id', $request->user()->id)
            ->where('token_hash', hash('sha256', $data['token']))
            ->delete();

        if ($deleted > 0) {
            SystemLog::query()->create([
                'user_id' => $request->user()->id,
                'action' => 'push_device_removed',
                'description' => 'Видалено push-пристрій під час виходу з мобільної CRM',
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'source' => 'mobile_app',
            ]);
        }

        return response()->json([
            'message' => 'Push-пристрій видалено.',
        ]);
    }
}
