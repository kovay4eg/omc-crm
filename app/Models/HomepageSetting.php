<?php

namespace App\Models;

use App\Models\Concerns\DeletesMediaFiles;
use Illuminate\Database\Eloquent\Model;

class HomepageSetting extends Model
{
    use DeletesMediaFiles;

    protected $fillable = [
        'banner_image',
        'mobile_banner_image',
        'logo',
        'contact_address',
        'contact_phone',
        'contact_email',
        'google_maps_url',
        'facebook_enabled',
        'facebook_url',
        'instagram_enabled',
        'instagram_url',
        'telegram_enabled',
        'telegram_url',
        'youtube_enabled',
        'youtube_url',
        'tiktok_enabled',
        'tiktok_url',
        'smm_title',
        'smm_description',
        'smm_image',
    ];

    protected function mediaFields(): array
    {
        return ['banner_image', 'mobile_banner_image', 'logo', 'smm_image'];
    }

    protected function optimizableMediaFields(): array
    {
        return ['banner_image', 'mobile_banner_image', 'smm_image'];
    }

    protected function mediaImageProfile(string $field): array
    {
        return $field === 'smm_image'
            ? ['max_width' => 1200, 'max_height' => 1200, 'quality' => 86]
            : ['max_width' => 2400, 'max_height' => 1800, 'quality' => 85];
    }
}
