<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class HomepageSettingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'banner_image_url' => $this->mediaUrl($request, $this->banner_image),
            'mobile_banner_image_url' => $this->mediaUrl($request, $this->mobile_banner_image),
            'logo_url' => $this->mediaUrl($request, $this->logo),
            'contact_address' => $this->contact_address,
            'contact_phone' => $this->contact_phone,
            'contact_email' => $this->contact_email,
            'google_maps_url' => $this->google_maps_url,
            'socials' => [
                'facebook' => ['enabled' => (bool) $this->facebook_enabled, 'url' => $this->facebook_url],
                'instagram' => ['enabled' => (bool) $this->instagram_enabled, 'url' => $this->instagram_url],
                'telegram' => ['enabled' => (bool) $this->telegram_enabled, 'url' => $this->telegram_url],
                'youtube' => ['enabled' => (bool) $this->youtube_enabled, 'url' => $this->youtube_url],
                'tiktok' => ['enabled' => (bool) $this->tiktok_enabled, 'url' => $this->tiktok_url],
            ],
            'smm' => [
                'title' => $this->smm_title,
                'description' => $this->smm_description,
                'image_url' => $this->mediaUrl($request, $this->smm_image),
            ],
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    private function mediaUrl(Request $request, ?string $path): ?string
    {
        return $path ? $request->getSchemeAndHttpHost().Storage::url($path) : null;
    }
}
