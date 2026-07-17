<?php

namespace App\Http\Controllers;

use App\Enums\EventStatus;
use App\Models\Event;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::query()
            ->withCount('registrations')
            ->whereDate('event_date', '>=', today())
            ->whereIn('status', [
                EventStatus::Published->value,
                EventStatus::Rescheduled->value,
                EventStatus::Cancelled->value,
            ])
            ->orderBy('event_date')
            ->get()
            ->each(function (Event $event) {
                $event->available_participants = $event->max_participants === null
                    ? null
                    : max(0, $event->max_participants - $event->registrations_count);

                $event->is_full = $event->max_participants !== null
                    && $event->available_participants === 0;

                $event->registration_is_available =
                    $event->has_registration_button
                    && $event->status !== EventStatus::Cancelled
                    && !$event->is_full;
            });

        return view('events.index', compact('events'));
    }
}