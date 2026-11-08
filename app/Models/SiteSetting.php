<?php

namespace App\Models;

use App\Models\Concerns\DeletesMediaFiles;
use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    use DeletesMediaFiles;

    protected $fillable = [
        'team_banner',
        'maintenance_mode',
        'mobile_maintenance_mode',
        'holiday_logos_enabled',
        'winter_decor_enabled',
    ];

    protected function casts(): array
    {
        return [
            'maintenance_mode' => 'boolean',
            'mobile_maintenance_mode' => 'boolean',
            'holiday_logos_enabled' => 'boolean',
            'winter_decor_enabled' => 'boolean',
        ];
    }

    protected function mediaFields(): array
    {
        return ['team_banner'];
    }

    protected function mediaImageProfile(string $field): array
    {
        return ['max_width' => 2400, 'max_height' => 1800, 'quality' => 85];
    }
}
