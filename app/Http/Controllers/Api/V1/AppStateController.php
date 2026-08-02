<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\AppAnnouncementResource;
use App\Models\AppAnnouncement;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppStateController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $settings = SiteSetting::query()->first();
        $announcement = AppAnnouncement::query()
            ->active()
            ->latest('starts_at')
            ->latest('id')
            ->first();

        return response()->json(['data' => [
            'site_maintenance_mode' => (bool) $settings?->maintenance_mode,
            'mobile_maintenance_mode' => (bool) $settings?->mobile_maintenance_mode,
            'can_manage' => $request->user()->isAdmin(),
            'announcement' => $announcement
                ? (new AppAnnouncementResource($announcement))->resolve($request)
                : null,
        ]]);
    }

    public function update(Request $request): JsonResponse
    {
        abort_unless($request->user()->isAdmin(), 403, 'Керування технічними режимами доступне лише адміністраторам.');

        $data = $request->validate([
            'site_maintenance_mode' => ['sometimes', 'boolean'],
            'mobile_maintenance_mode' => ['sometimes', 'boolean'],
        ]);
        abort_if($data === [], 422, 'Не передано жодного налаштування.');

        $settings = SiteSetting::query()->first() ?? new SiteSetting();
        if (array_key_exists('site_maintenance_mode', $data)) {
            $settings->maintenance_mode = $data['site_maintenance_mode'];
        }
        if (array_key_exists('mobile_maintenance_mode', $data)) {
            $settings->mobile_maintenance_mode = $data['mobile_maintenance_mode'];
        }
        $settings->save();

        foreach ($data as $name => $enabled) {
            $label = $name === 'site_maintenance_mode' ? 'сайту' : 'мобільного застосунку';
            system_log(
                'toggle_'.$name,
                ($enabled ? 'Увімкнено' : 'Вимкнено').' технічний режим '.$label.'.',
            );
        }

        return $this->show($request);
    }
}
