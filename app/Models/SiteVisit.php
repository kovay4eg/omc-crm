<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteVisit extends Model
{
    protected $fillable = [
        'session_hash',
        'visited_on',
        'last_seen_at',
    ];

    protected function casts(): array
    {
        return [
            'visited_on' => 'date',
            'last_seen_at' => 'datetime',
        ];
    }
}
