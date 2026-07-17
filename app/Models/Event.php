<?php

namespace App\Models;

use App\Enums\EventStatus;
use Illuminate\Database\Eloquent\Model;

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

        'cancel_reason',
        'cancel_public',
        'cancelled_at',

        'old_event_date',
        'rescheduled_at',
        'reschedule_reason',
        'reschedule_public',
    ];

    protected $casts = [
        'event_date' => 'datetime',
        'old_event_date' => 'datetime',
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
        return $this->hasMany(EventHistory::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}