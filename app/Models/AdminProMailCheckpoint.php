<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminProMailCheckpoint extends Model
{
    protected $fillable = [
        'mailbox',
        'uid_validity',
        'last_uid',
        'checked_at',
    ];

    protected function casts(): array
    {
        return [
            'uid_validity' => 'integer',
            'last_uid' => 'integer',
            'checked_at' => 'datetime',
        ];
    }
}
