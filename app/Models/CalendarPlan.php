<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalendarPlan extends Model
{
    protected $fillable = [
        'title',
        'year',
        'file',
    ];
}