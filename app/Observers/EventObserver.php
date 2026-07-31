<?php

namespace App\Observers;

use App\Models\Event;
use App\Services\GoogleCalendarService;

class EventObserver
{
    protected $google;

    public function __construct()
    {
        $this->google = app(GoogleCalendarService::class);
    }

    public function updated(Event $event): void
    {
        if (!$event->user) return;

        // якщо скасовано
        if ($event->status === 'cancelled') {
            $this->google->deleteEvent($event->user, $event);
            return;
        }

        $this->google->updateEvent($event->user, $event);
    }

    public function deleted(Event $event): void
    {
        if (!$event->user) return;

        $this->google->deleteEvent($event->user, $event);
    }
}