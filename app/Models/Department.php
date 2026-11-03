<?php

namespace App\Models;

use App\Models\Concerns\DeletesMediaFiles;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use DeletesMediaFiles;

    protected $fillable = [
        'name',
        'description',
        'photo',
        'slider_enabled',
        'slider_photos',
    ];

    protected function casts(): array
    {
        return ['slider_enabled' => 'boolean', 'slider_photos' => 'array'];
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    protected function mediaFields(): array
    {
        return ['photo', 'slider_photos'];
    }
}
