<?php

namespace App\Observers;

use App\Enums\EventStatus;
use App\Models\Event;
use App\Services\GoogleCalendarService;

class EventObserver
{
    protected $google;

    public function __construct()
    {
        $this->google = app(GoogleCalendarService::class);
    }

    public function saving(Event $event): void
    {
        if ($event->registration_type !== 'external' || blank($event->google_sheet_url)) {
            $event->google_sheet_url = null;
            $event->google_sheet_name = null;
            $event->google_sync_user_id = null;

            return;
        }

        $user = auth()->user();

        if ($user?->hasGoogleConnection() && ($event->isDirty('google_sheet_url') || blank($event->google_sync_user_id))) {
            $event->google_sync_user_id = $user->getKey();
        }
    }

    public function updated(Event $event): void
    {
        if (! $event->user) {
            return;
        }

        // якщо скасовано
        if ($event->status === EventStatus::Cancelled) {
            $this->google->deleteEvent($event->user, $event);

            return;
        }

        $this->google->updateEvent($event->user, $event);
    }

    public function created(Event $event): void
    {
        if ($event->user) {
            $this->google->createEvent($event->user, $event);
        }
    }

    public function deleted(Event $event): void
    {
        if (! $event->user) {
            return;
        }

        $this->google->deleteEvent($event->user, $event);
    }
}
