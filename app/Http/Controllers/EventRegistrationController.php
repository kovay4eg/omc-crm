<?php

namespace App\Http\Controllers;

use App\Enums\EventStatus;
use App\Mail\NewRegistrationNotification;
use App\Models\Event;
use App\Models\Registration;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class EventRegistrationController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'event_id' => ['required', 'integer', 'exists:events,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
        ]);

        try {
            $registration = DB::transaction(function () use ($validated) {
                $event = Event::query()
                    ->lockForUpdate()
                    ->findOrFail($validated['event_id']);

                if (
                    !in_array(
                        $event->status,
                        [EventStatus::Published, EventStatus::Rescheduled],
                        true
                    )
                ) {
                    throw ValidationException::withMessages([
                        'event_id' => 'Реєстрацію на цей захід закрито.',
                    ]);
                }

                if (
                    !$event->has_registration_button ||
                    $event->registration_type !== 'internal'
                ) {
                    throw ValidationException::withMessages([
                        'event_id' => 'Онлайн-реєстрація на цей захід недоступна.',
                    ]);
                }

                $alreadyRegistered = $event->registrations()
                    ->where(function ($query) use ($validated) {
                        $query->where('email', $validated['email'])
                            ->orWhere('phone', $validated['phone']);
                    })
                    ->exists();

                if ($alreadyRegistered) {
                    throw ValidationException::withMessages([
                        'email' => 'Ви вже зареєстровані на цей захід.',
                    ]);
                }

                if (
                    $event->max_participants !== null &&
                    $event->registrations()->count() >= $event->max_participants
                ) {
                    throw ValidationException::withMessages([
                        'limit' => 'Реєстрацію закрито: вільних місць немає.',
                    ]);
                }

                $registration = Registration::create([
                    'event_id' => $event->id,
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'],
                    'source' => 'internal',
                ]);

                if ($event->notify_email) {
                    Mail::to($event->notify_email)
                        ->send(new NewRegistrationNotification($registration, $event));
                }

                return $registration;
            });
        } catch (QueryException $exception) {
            if ($exception->getCode() === '23000') {
                throw ValidationException::withMessages([
                    'email' => 'Ви вже зареєстровані на цей захід.',
                ]);
            }

            throw $exception;
        }

        return response()->json([
            'message' => 'Реєстрація успішна.',
            'registration_id' => $registration->id,
        ], 201);
    }
}