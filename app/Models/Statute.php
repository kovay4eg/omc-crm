<?php

namespace App\Models;

use App\Models\Concerns\DeletesMediaFiles;
use Illuminate\Database\Eloquent\Model;

class Statute extends Model
{
    use DeletesMediaFiles;

    protected $fillable = [
        'title',
        'file',
    ];

    protected function mediaFields(): array
    {
        return ['file'];
    }
}
