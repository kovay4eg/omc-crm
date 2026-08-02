<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\HomepageSettingResource;
use App\Models\HomepageSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class HomepageSettingsController extends Controller
{
    public function show(): HomepageSettingResource
    {
        $settings = HomepageSetting::query()->firstOrCreate([]);

        return new HomepageSettingResource(HomepageSetting::query()->findOrFail($settings->id));
    }

    public function update(Request $request): HomepageSettingResource
    {
        $this->authorizeAdmin($request);
        $data = $request->validate($this->rules());
        $settings = HomepageSetting::query()->firstOrCreate([]);
        $attributes = Arr::except($data, [
            'banner_image_file', 'mobile_banner_image_file', 'logo_file', 'smm_image_file',
            'remove_banner_image', 'remove_mobile_banner_image', 'remove_logo', 'remove_smm_image',
        ]);

        foreach ([
            'banner_image' => 'banner_image_file',
            'mobile_banner_image' => 'mobile_banner_image_file',
            'logo' => 'logo_file',
            'smm_image' => 'smm_image_file',
        ] as $field => $upload) {
            if ($request->boolean('remove_'.$field)) {
                $attributes[$field] = null;
            }

            if ($request->hasFile($upload)) {
                $attributes[$field] = $request->file($upload)->store(
                    $field === 'smm_image' ? 'smm' : 'homepage',
                    'public',
                );
            }
        }

        $settings->fill($attributes)->save();
        system_log('update_homepage_settings', 'Оновлено налаштування головної сторінки з мобільного застосунку.');

        return new HomepageSettingResource(HomepageSetting::query()->findOrFail($settings->id));
    }

    private function rules(): array
    {
        $rules = [
            'contact_address' => ['nullable', 'string', 'max:500'],
            'contact_phone' => ['nullable', 'string', 'max:80'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'google_maps_url' => ['nullable', 'url:http,https', 'max:2048'],
            'smm_title' => ['nullable', 'string', 'max:255'],
            'smm_description' => ['nullable', 'string', 'max:500'],
        ];

        foreach (['facebook', 'instagram', 'telegram', 'youtube', 'tiktok'] as $social) {
            $rules[$social.'_enabled'] = ['nullable', 'boolean'];
            $rules[$social.'_url'] = ['nullable', 'url:http,https', 'max:2048'];
        }

        foreach (['banner_image', 'mobile_banner_image', 'logo', 'smm_image'] as $field) {
            $rules[$field.'_file'] = ['nullable', 'image', 'max:10240'];
            $rules['remove_'.$field] = ['nullable', 'boolean'];
        }

        return $rules;
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user()->isAdmin(), 403, 'Налаштування головної доступні лише адміністраторам.');
    }
}
