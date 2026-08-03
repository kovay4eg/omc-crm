<?php

namespace App\Http\Controllers;

use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\HomepageSetting;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

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
                    && ! $event->is_full;
            });

        return view('events.index', compact('events'));
    }

    public function show(Event $event): Response
    {
        abort_unless(
            $event->event_date->gte(today())
                && in_array($event->status, [
                    EventStatus::Published,
                    EventStatus::Rescheduled,
                    EventStatus::Cancelled,
                ], true),
            404,
        );

        $event->loadCount('registrations');

        $event->available_participants = $event->max_participants === null
            ? null
            : max(0, $event->max_participants - $event->registrations_count);

        $event->is_full = $event->max_participants !== null
            && $event->available_participants === 0;

        $event->registration_is_available =
            $event->has_registration_button
            && $event->status !== EventStatus::Cancelled
            && ! $event->is_full;

        $settings = HomepageSetting::first();
        $fallbackImage = $settings?->smm_image
            ?: $settings?->banner_image
            ?: $settings?->logo;

        return response()->view('events.show', [
            'event' => $event,
            'smmTitle' => $event->smm_title ?: $event->title,
            'smmDescription' => $event->smm_description
                ?: Str::limit(
                    preg_replace('/\s+/', ' ', trim($event->description)),
                    180,
                ),
            'smmImage' => $event->smm_image ?: $event->image ?: $fallbackImage,
        ]);
    }
}
