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
    ];

    protected function casts(): array
    {
        return [
            'maintenance_mode' => 'boolean',
        ];
    }

    protected function mediaFields(): array
    {
        return ['team_banner'];
    }
}
