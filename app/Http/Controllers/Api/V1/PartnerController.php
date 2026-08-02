<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\FooterSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PartnerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->items($request, FooterSetting::query()->first())]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'logos' => ['required', 'array', 'min:1', 'max:5'],
            'logos.*' => ['image', 'max:8192'],
        ]);
        $settings = FooterSetting::query()->firstOrCreate([]);
        $logos = $settings->partner_logos ?? [];
        abort_if(count($logos) + count($data['logos']) > 5, 422, 'Можна зберегти не більше 5 логотипів.');
        foreach ($request->file('logos') as $logo) {
            $logos[] = $logo->store('footer-partners', 'public');
        }
        $settings->partner_logos = $logos;
        $settings->save();
        system_log('update_footer_partners', 'Додано логотипи партнерів з мобільного застосунку.');

        return response()->json(['data' => $this->items($request, $settings->refresh())]);
    }

    public function destroy(Request $request, int $index): JsonResponse
    {
        $settings = FooterSetting::query()->firstOrFail();
        $logos = array_values($settings->partner_logos ?? []);
        abort_unless(array_key_exists($index, $logos), 404, 'Логотип не знайдено.');
        array_splice($logos, $index, 1);
        $settings->partner_logos = $logos;
        $settings->save();
        system_log('update_footer_partners', 'Видалено логотип партнера з мобільного застосунку.');

        return response()->json(['data' => $this->items($request, $settings->refresh())]);
    }

    private function items(Request $request, ?FooterSetting $settings): array
    {
        return collect($settings?->partner_logos ?? [])->values()->map(fn (string $path, int $index) => [
            'index' => $index,
            'image_url' => $request->getSchemeAndHttpHost().Storage::url($path),
        ])->all();
    }
}
