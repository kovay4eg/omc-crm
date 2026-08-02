<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'description',
        'ip',
        'user_agent',
        'source',
        'device_name',
        'platform',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
