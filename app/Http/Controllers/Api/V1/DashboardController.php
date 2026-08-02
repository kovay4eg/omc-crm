<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\EventStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\DashboardEventResource;
use App\Models\Event;
use App\Models\EventSummary;
use App\Models\Registration;
use App\Models\SiteVisit;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $today = now('Europe/Kyiv')->startOfDay();
        $onlineSince = now('Europe/Kyiv')->subMinutes(5);

        $activeEventsQuery = Event::query()
            ->where('event_date', '>=', $today)
            ->whereIn('status', [
                EventStatus::Published->value,
                EventStatus::Rescheduled->value,
            ]);

        $upcomingEvents = (clone $activeEventsQuery)
            ->with('user:id,name')
            ->withCount('registrations')
            ->orderBy('event_date')
            ->limit(10)
            ->get();

        return response()->json([
            'data' => [
                'stats' => [
                    'events_total' => Event::query()->count(),
                    'active_events' => (clone $activeEventsQuery)->count(),
                    'draft_events' => Event::query()
                        ->where('status', EventStatus::Draft->value)
                        ->count(),
                    'cancelled_events' => Event::query()
                        ->where('status', EventStatus::Cancelled->value)
                        ->count(),
                    'registrations_total' => Registration::query()->count(),
                    'published_summaries' => EventSummary::query()
                        ->where('status', EventSummary::STATUS_PUBLISHED)
                        ->count(),
                    'users_total' => User::query()->count(),
                    'visitors_today' => SiteVisit::query()
                        ->whereDate('visited_on', $today->toDateString())
                        ->count(),
                    'online_now' => SiteVisit::query()
                        ->where('last_seen_at', '>=', $onlineSince)
                        ->count(),
                ],
                'upcoming_events' => DashboardEventResource::collection($upcomingEvents),
                'generated_at' => now('Europe/Kyiv')->toIso8601String(),
            ],
        ]);
    }
}
