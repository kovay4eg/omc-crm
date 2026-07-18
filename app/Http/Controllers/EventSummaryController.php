<?php

namespace App\Http\Controllers;

use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\EventSummary;
use Illuminate\Http\Response;

class EventSummaryController extends Controller
{
    public function show(EventSummary $eventSummary): Response
    {
        $eventSummary->loadMissing([
            'event',
            'images',
        ]);

        abort_unless($this->canBeShownPublicly($eventSummary), 404);

        return response()->view('event_summaries.show', [
            'summary' => $eventSummary,
            'isPreview' => false,
        ]);
    }

    public function preview(Event $event): Response
    {
        abort_unless(in_array(auth()->user()?->getActiveRole(), [
            'admin',
            'editor',
            'content',
        ]), 403);

        $summary = $event->summary()
            ->with([
                'event',
                'images',
            ])
            ->first();

        abort_unless($summary, 404);

        return response()->view('event_summaries.show', [
            'summary' => $summary,
            'isPreview' => true,
        ]);
    }

    protected function canBeShownPublicly(EventSummary $summary): bool
    {
        return $summary->isPublished()
            && $summary->event !== null
            && $summary->event->event_date->lt(today())
            && in_array($summary->event->status, [
                EventStatus::Published,
                EventStatus::Rescheduled,
            ], true);
    }
}
