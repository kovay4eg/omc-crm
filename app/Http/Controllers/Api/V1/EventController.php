<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\EventStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CancelEventRequest;
use App\Http\Requests\Api\V1\EventRequest;
use App\Http\Requests\Api\V1\RescheduleEventRequest;
use App\Http\Resources\Api\V1\EventResource;
use App\Models\Event;
use App\Models\EventHistory;
use App\Services\GoogleCalendarService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:draft,published,cancelled,rescheduled'],
            'scope' => ['nullable', 'in:upcoming,past,all'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'author_id' => ['nullable', 'integer', 'exists:users,id'],
            'sort' => ['nullable', 'in:event_date,title,created_at'],
            'direction' => ['nullable', 'in:asc,desc'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $query = Event::query()
            ->with('user:id,name')
            ->withCount('registrations')
            ->when($filters['search'] ?? null, fn ($query, $search) => $query
                ->where(fn ($query) => $query
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['author_id'] ?? null, fn ($query, $authorId) => $query->where('user_id', $authorId))
            ->when($filters['date_from'] ?? null, fn ($query, $date) => $query->whereDate('event_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, $date) => $query->whereDate('event_date', '<=', $date));

        match ($filters['scope'] ?? 'upcoming') {
            'past' => $query->where('event_date', '<', now()),
            'all' => null,
            default => $query->where('event_date', '>=', now()->startOfDay()),
        };

        return EventResource::collection($query
            ->orderBy($filters['sort'] ?? 'event_date', $filters['direction'] ?? 'asc')
            ->paginate($filters['per_page'] ?? 15)
            ->withQueryString());
    }

    public function store(EventRequest $request): EventResource
    {
        $event = DB::transaction(function () use ($request): Event {
            $data = $this->eventData($request);
            $data['user_id'] = $request->user()->id;
            $event = Event::query()->create($data);

            EventHistory::query()->create([
                'event_id' => $event->id,
                'user_id' => $request->user()->id,
                'action' => 'created',
                'description' => 'Створення події',
                'new_date' => $event->event_date,
                'is_public' => false,
            ]);

            system_log('create_event', 'Створено подію: '.$event->title);

            return $event;
        });

        app(GoogleCalendarService::class)->createEvent($request->user(), $event);

        return new EventResource($this->loadEvent($event));
    }

    public function show(Event $event): EventResource
    {
        return new EventResource($this->loadEvent($event));
    }

    public function update(EventRequest $request, Event $event): EventResource
    {
        $data = $this->eventData($request, $event);
        $event->update($data);

        $changedFields = array_keys(Arr::except($event->getChanges(), ['updated_at']));

        if ($changedFields !== []) {
            EventHistory::query()->create([
                'event_id' => $event->id,
                'user_id' => $request->user()->id,
                'action' => 'updated',
                'description' => 'Оновлено: '.implode(', ', $changedFields),
                'old_date' => $event->getOriginal('event_date'),
                'new_date' => $event->event_date,
                'is_public' => false,
            ]);
            system_log('update_event', 'Оновлено подію: '.$event->title);
        }

        return new EventResource($this->loadEvent($event));
    }

    public function destroy(Request $request, Event $event)
    {
        abort_unless($request->user()->isAdmin(), 403, 'Видаляти події може лише адміністратор.');

        $title = $event->title;
        $event->delete();
        system_log('delete_event', 'Видалено подію: '.$title);

        return response()->noContent();
    }

    public function cancel(CancelEventRequest $request, Event $event): EventResource
    {
        abort_if($event->status === EventStatus::Cancelled, 422, 'Подію вже скасовано.');

        $event->histories()->create([
            'user_id' => $request->user()->id,
            'action' => 'cancelled',
            'description' => $request->string('reason'),
            'old_date' => $event->event_date,
            'is_public' => $request->boolean('cancel_public'),
        ]);
        $event->update([
            'status' => EventStatus::Cancelled,
            'cancel_reason' => $request->string('reason'),
            'cancel_public' => $request->boolean('cancel_public'),
            'cancelled_at' => now(),
        ]);
        system_log('cancel_event', 'Скасовано подію: '.$event->title);

        return new EventResource($this->loadEvent($event));
    }

    public function reschedule(RescheduleEventRequest $request, Event $event): EventResource
    {
        abort_if($event->status === EventStatus::Cancelled, 422, 'Скасовану подію не можна перенести.');

        $oldDate = $event->event_date;
        $event->histories()->create([
            'user_id' => $request->user()->id,
            'action' => 'rescheduled',
            'description' => $request->string('reason'),
            'old_date' => $oldDate,
            'new_date' => $request->date('new_date'),
            'is_public' => $request->boolean('reschedule_public'),
        ]);
        $event->update([
            'event_date' => $request->date('new_date'),
            'status' => EventStatus::Rescheduled,
            'old_event_date' => $oldDate,
            'rescheduled_at' => now(),
            'reschedule_reason' => $request->string('reason'),
            'reschedule_public' => $request->boolean('reschedule_public'),
        ]);
        system_log('reschedule_event', 'Перенесено подію: '.$event->title);

        return new EventResource($this->loadEvent($event));
    }

    private function eventData(EventRequest $request, ?Event $event = null): array
    {
        $data = Arr::except($request->validated(), [
            'image', 'smm_image', 'remove_image', 'remove_smm_image',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('events', 'public');
        } elseif ($request->boolean('remove_image')) {
            $data['image'] = null;
        }

        if ($request->hasFile('smm_image')) {
            $data['smm_image'] = $request->file('smm_image')->store('smm/events', 'public');
        } elseif ($request->boolean('remove_smm_image')) {
            $data['smm_image'] = null;
        }

        $registrationEnabled = $data['has_registration_button'] ?? $event?->has_registration_button ?? false;

        if (! $registrationEnabled) {
            $data['registration_type'] = 'none';
            $data['google_form_url'] = null;
        } elseif (($data['registration_type'] ?? $event?->registration_type) !== 'external') {
            $data['google_form_url'] = null;
        }

        return $data;
    }

    private function loadEvent(Event $event): Event
    {
        return $event->refresh()->load('user:id,name')->loadCount('registrations');
    }
}
