<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MobilePushDevice extends Model
{
    protected $fillable = [
        'user_id',
        'token_hash',
        'token',
        'platform',
        'device_name',
        'preferences',
        'last_seen_at',
    ];

    protected function casts(): array
    {
        return [
            'token' => 'encrypted',
            'preferences' => 'array',
            'last_seen_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
