<?php

namespace App\Models;

use App\Models\Concerns\DeletesMediaFiles;
use Illuminate\Database\Eloquent\Model;

class FooterSetting extends Model
{
    use DeletesMediaFiles;

    protected $fillable = [
        'partner_logos',
    ];

    protected function casts(): array
    {
        return [
            'partner_logos' => 'array',
        ];
    }

    protected function mediaFields(): array
    {
        return ['partner_logos'];
    }
}
