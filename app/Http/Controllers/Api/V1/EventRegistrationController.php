<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\EventStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\RegistrationRequest;
use App\Http\Resources\Api\V1\RegistrationResource;
use App\Models\Event;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EventRegistrationController extends Controller
{
    public function index(Request $request, Event $event): AnonymousResourceCollection
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $registrations = $event->registrations()
            ->when($filters['search'] ?? null, fn ($query, $search) => $query
                ->where(fn ($query) => $query
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")))
            ->latest()
            ->paginate($filters['per_page'] ?? 25)
            ->withQueryString();

        return RegistrationResource::collection($registrations);
    }

    public function store(RegistrationRequest $request, Event $event): RegistrationResource
    {
        $registration = DB::transaction(function () use ($request, $event): Registration {
            $lockedEvent = Event::query()->lockForUpdate()->findOrFail($event->id);

            if ($lockedEvent->status === EventStatus::Cancelled) {
                throw ValidationException::withMessages([
                    'event' => ['Не можна додати учасника до скасованої події.'],
                ]);
            }

            if (
                $lockedEvent->max_participants !== null
                && $lockedEvent->registrations()->count() >= $lockedEvent->max_participants
            ) {
                throw ValidationException::withMessages([
                    'limit' => ['Вільних місць немає.'],
                ]);
            }

            return $lockedEvent->registrations()->create([
                ...$request->validated(),
                'source' => 'internal',
            ]);
        });

        system_log('create_registration', 'Додано учасника до події: '.$event->title);

        return new RegistrationResource($registration);
    }

    public function update(
        RegistrationRequest $request,
        Event $event,
        Registration $registration,
    ): RegistrationResource {
        $this->ensureBelongsToEvent($event, $registration);
        $registration->update($request->validated());
        system_log('update_registration', 'Оновлено учасника події: '.$event->title);

        return new RegistrationResource($registration->refresh());
    }

    public function destroy(Event $event, Registration $registration)
    {
        $this->ensureBelongsToEvent($event, $registration);
        $registration->delete();
        system_log('delete_registration', 'Видалено учасника події: '.$event->title);

        return response()->noContent();
    }

    private function ensureBelongsToEvent(Event $event, Registration $registration): void
    {
        abort_unless($registration->event_id === $event->id, 404);
    }
}
