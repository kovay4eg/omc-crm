<?php

namespace App\Models;

use App\Enums\EventStatus;
use App\Models\Concerns\DeletesMediaFiles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Event extends Model
{
    use DeletesMediaFiles;

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
        'google_sheet_url',
        'google_sheet_name',
        'google_sync_user_id',
        'google_last_synced_at',
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

        'smm_title',
        'smm_description',
        'smm_image',
    ];

    protected $casts = [
        'event_date' => 'datetime',
        'old_event_date' => 'datetime',
        'cancelled_at' => 'datetime',
        'rescheduled_at' => 'datetime',
        'cancel_public' => 'boolean',
        'reschedule_public' => 'boolean',
        'google_last_synced_at' => 'datetime',
        'status' => EventStatus::class,
    ];

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(EventHistory::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function googleSyncUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'google_sync_user_id');
    }

    public function summary(): HasOne
    {
        return $this->hasOne(EventSummary::class);
    }

    protected function mediaFields(): array
    {
        return ['image', 'smm_image'];
    }

    protected function mediaImageProfile(string $field): array
    {
        return $field === 'smm_image'
            ? ['max_width' => 1200, 'max_height' => 1200, 'quality' => 86]
            : ['max_width' => 1600, 'max_height' => 2000, 'quality' => 88];
    }
}
