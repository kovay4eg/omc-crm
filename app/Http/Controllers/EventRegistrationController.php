<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use App\Mail\NewRegistrationNotification;

class EventRegistrationController extends Controller
{
    public function store(Request $request, Event $event)
    {
        // ❌ якщо реєстрація вимкнена
        if (!$event->has_registration) {
            return back()->with('error', 'Реєстрація закрита');
        }

        // ❌ якщо івент скасований
        if ($event->status === 'cancelled') {
            return back()->with('error', 'Цей захід скасовано');
        }

        // ✅ валідація
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string|max:20',
        ]);

        DB::transaction(function () use ($event, $validated) {

            // 🔒 блокуємо запис
            $event->lockForUpdate();

            // ❌ перевірка дубля (email або телефон)
            if (
                $event->registrations()
                    ->where(function ($q) use ($validated) {
                        $q->where('email', $validated['email'])
                          ->orWhere('phone', $validated['phone']);
                    })
                    ->exists()
            ) {
                throw ValidationException::withMessages([
                    'email' => 'Ви вже зареєстровані на цей захід',
                ]);
            }

            // ❌ перевірка ліміту
            if (
                $event->max_participants &&
                $event->registrations()->count() >= $event->max_participants
            ) {
                throw ValidationException::withMessages([
                    'limit' => 'Досягнуто ліміту учасників',
                ]);
            }

            // ✅ створення реєстрації
            $registration = Registration::create([
                'event_id' => $event->id,
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'source' => $event->registration_type,
            ]);

            // 📧 email адміну (якщо вказаний)
            if ($event->notify_email) {
                Mail::to($event->notify_email)
                    ->send(new NewRegistrationNotification($registration, $event));
            }
        });

        return back()->with('success', 'Реєстрація успішна');
    }
}