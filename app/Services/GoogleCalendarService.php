<?php

namespace App\Services;

use App\Models\Event as EventModel;
use App\Models\User;
use Carbon\Carbon;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\EventDateTime;
use Throwable;

class GoogleCalendarService
{
    public function __construct(private readonly GoogleApiClientFactory $clients) {}

    public function createEvent(User $user, EventModel $event): bool
    {
        if (! $user->hasGoogleConnection() || filled($event->google_event_id)) {
            return false;
        }

        try {
            $service = new Calendar($this->clients->make($user));
            $createdEvent = $service->events->insert('primary', $this->googleEvent($event));

            $event->forceFill(['google_event_id' => $createdEvent->getId()])->saveQuietly();

            return true;
        } catch (Throwable $exception) {
            report($exception);

            return false;
        }
    }

    public function updateEvent(User $user, EventModel $event): bool
    {
        if (! $user->hasGoogleConnection()) {
            return false;
        }

        if (blank($event->google_event_id)) {
            return $this->createEvent($user, $event);
        }

        try {
            $service = new Calendar($this->clients->make($user));
            $service->events->update(
                'primary',
                $event->google_event_id,
                $this->googleEvent($event),
            );

            return true;
        } catch (Throwable $exception) {
            report($exception);

            return false;
        }
    }

    public function deleteEvent(User $user, EventModel $event): bool
    {
        if (! $user->hasGoogleConnection() || blank($event->google_event_id)) {
            return false;
        }

        try {
            $service = new Calendar($this->clients->make($user));
            $service->events->delete('primary', $event->google_event_id);

            return true;
        } catch (Throwable $exception) {
            report($exception);

            return false;
        }
    }

    private function googleEvent(EventModel $event): Event
    {
        $start = Carbon::parse($event->event_date)->setTimezone('Europe/Kyiv');

        return new Event([
            'summary' => $event->title,
            'description' => $event->description,
            'start' => $this->dateTime($start),
            'end' => $this->dateTime($start->copy()->addHour()),
        ]);
    }

    private function dateTime(Carbon $date): EventDateTime
    {
        return new EventDateTime([
            'dateTime' => $date->toRfc3339String(),
            'timeZone' => 'Europe/Kyiv',
        ]);
    }
}
