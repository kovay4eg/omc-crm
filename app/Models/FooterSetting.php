<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FooterSetting extends Model
{
    protected $fillable = [
        'partner_logos',
    ];

    protected function casts(): array
    {
        return [
            'partner_logos' => 'array',
        ];
    }
}
