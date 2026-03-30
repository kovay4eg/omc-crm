<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\EventStatus;

class Event extends Model
{
    protected $fillable = [
        'title',
        'description',
        'event_date',
        'image',
        'status',
        'notify_email',
        'has_registration_button',
        'registration_type',
        'google_form_url',
        'max_participants',
        'show_available_slots',
        'user_id',

        // cancel
        'cancel_reason',
        'cancel_public',
        'cancelled_at',

        // reschedule
        'rescheduled_at',
        'reschedule_reason',
        'reschedule_public',
    ];

    protected $casts = [
        'event_date' => 'datetime',
        'cancelled_at' => 'datetime',
        'rescheduled_at' => 'datetime',
        'cancel_public' => 'boolean',
        'reschedule_public' => 'boolean',
        'status' => EventStatus::class,
    ];

    public function registrations()
    {
        return $this->hasMany(Registration::class);
    }

    
    public function histories()
    {
        return $this->hasMany(\App\Models\EventHistory::class);
    }
    public function user()
{
    return $this->belongsTo(\App\Models\User::class);
}
}