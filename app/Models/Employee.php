<?php

namespace App\Models;

use App\Models\Concerns\DeletesMediaFiles;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use DeletesMediaFiles;

    protected $fillable = [
        'first_name',
        'last_name',
        'middle_name',
        'position_id',
        'department_id',
        'photo',
        'sort',
    ];

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    protected function mediaFields(): array
    {
        return ['photo'];
    }
}
