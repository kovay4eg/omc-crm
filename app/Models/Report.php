<?php

namespace App\Models;

use App\Models\Concerns\DeletesMediaFiles;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use DeletesMediaFiles;

    protected $fillable = [
        'title',
        'year',
        'file',
    ];

    protected function mediaFields(): array
    {
        return ['file'];
    }
}
