<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomepageSetting extends Model
{
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
    ];
}
