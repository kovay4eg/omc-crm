<?php

namespace App\Http\Controllers;

use App\Enums\EventStatus;
use App\Models\CalendarPlan;
use App\Models\Department;
use App\Models\Event;
use App\Models\EventSummary;
use App\Models\HomepageSetting;
use App\Models\Report;
use App\Models\SiteSetting;

class HomeController extends Controller
{
    public function index()
    {
        $settings = HomepageSetting::first();

        $departments = Department::with('employees.position')->get();

        $siteSettings = SiteSetting::first();

        $reports = Report::orderBy('year', 'desc')->get();

        $calendarPlans = CalendarPlan::orderBy('year', 'desc')->get();

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

        $eventSummaries = EventSummary::query()
            ->with([
                'event',
                'images',
            ])
            ->where('status', EventSummary::STATUS_PUBLISHED)
            ->whereHas('event', function ($query) {
                $query
                    ->whereDate('event_date', '<', today())
                    ->whereIn('status', [
                        EventStatus::Published->value,
                        EventStatus::Rescheduled->value,
                    ]);
            })
            ->get()
            ->sortByDesc(fn (EventSummary $summary) => $summary->event->event_date)
            ->values();

        return view('index', [
            'settings' => $settings,
            'departments' => $departments,
            'siteSettings' => $siteSettings,
            'teamSettings' => $siteSettings,
            'reports' => $reports,
            'calendarPlans' => $calendarPlans,
            'events' => $events,
            'eventSummaries' => $eventSummaries,
        ]);
    }
}
