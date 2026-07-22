<?php

namespace App\Http\Controllers;

use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\EventSummary;
use App\Support\MediaStorage;

class EventImageController extends Controller
{
    public function show(Event $event)
    {
        $isActiveUpcomingEvent = $event->event_date->gte(today())
            && in_array($event->status, [
                EventStatus::Published,
                EventStatus::Rescheduled,
                EventStatus::Cancelled,
            ], true);

        $hasPublishedSummary = $event->summary()
            ->where('status', EventSummary::STATUS_PUBLISHED)
            ->exists();

        abort_unless(
            ($isActiveUpcomingEvent || $hasPublishedSummary || auth()->check())
                && filled($event->image),
            404,
        );

        return MediaStorage::response($event->image);
    }
}
