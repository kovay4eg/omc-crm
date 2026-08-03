<?php

namespace App\Services;

use App\Models\Event as EventModel;
use Carbon\Carbon;
use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\EventDateTime;

class GoogleCalendarService
{
    protected function getClient($user)
    {
        try {
            if (! $user->google_token) {
                return null;
            }

            $client = new Client;

            $client->setClientId(config('services.google.client_id'));
            $client->setClientSecret(config('services.google.client_secret'));
            $client->setRedirectUri(config('services.google.redirect'));

            $client->addScope(Calendar::CALENDAR);

            $client->setAccessToken([
                'access_token' => $user->google_token,
                'refresh_token' => $user->google_refresh_token,
                'expires_in' => now()->diffInSeconds($user->google_token_expires_at),
            ]);

            // 🔄 REFRESH TOKEN
            if ($client->isAccessTokenExpired()) {

                if (! $client->getRefreshToken()) {
                    return null;
                }

                $newToken = $client->fetchAccessTokenWithRefreshToken(
                    $client->getRefreshToken()
                );

                if (isset($newToken['access_token'])) {
                    $user->google_token = $newToken['access_token'];

                    if (isset($newToken['expires_in'])) {
                        $user->google_token_expires_at = now()->addSeconds($newToken['expires_in']);
                    }

                    $user->save();
                } else {
                    return null;
                }
            }

            return $client;

        } catch (\Throwable $e) {
            return null;
        }
    }

    // ➕ CREATE EVENT
    public function createEvent($user, EventModel $event)
    {
        try {
            $client = $this->getClient($user);
            if (! $client) {
                return false;
            }

            $service = new Calendar($client);

            $googleEvent = new Event([
                'summary' => $event->title,
                'description' => $event->description,
            ]);

            $start = new EventDateTime;
            $start->setDateTime(
                Carbon::parse($event->event_date)
                    ->setTimezone('Europe/Kyiv')
                    ->toRfc3339String()
            );
            $start->setTimeZone('Europe/Kyiv');

            $end = new EventDateTime;
            $end->setDateTime(
                Carbon::parse($event->event_date)
                    ->addHour()
                    ->setTimezone('Europe/Kyiv')
                    ->toRfc3339String()
            );
            $end->setTimeZone('Europe/Kyiv');

            $googleEvent->setStart($start);
            $googleEvent->setEnd($end);

            $createdEvent = $service->events->insert('primary', $googleEvent);

            $event->google_event_id = $createdEvent->id;
            $event->save();

            return true;

        } catch (\Throwable $e) {
            return false;
        }
    }

    // 🔄 UPDATE EVENT
    public function updateEvent($user, EventModel $event)
    {
        try {
            if (! $event->google_event_id) {
                return true;
            }

            $client = $this->getClient($user);
            if (! $client) {
                return false;
            }

            $service = new Calendar($client);

            $googleEvent = $service->events->get('primary', $event->google_event_id);

            $googleEvent->setSummary($event->title);
            $googleEvent->setDescription($event->description);

            $start = new EventDateTime;
            $start->setDateTime(
                Carbon::parse($event->event_date)
                    ->setTimezone('Europe/Kyiv')
                    ->toRfc3339String()
            );
            $start->setTimeZone('Europe/Kyiv');

            $end = new EventDateTime;
            $end->setDateTime(
                Carbon::parse($event->event_date)
                    ->addHour()
                    ->setTimezone('Europe/Kyiv')
                    ->toRfc3339String()
            );
            $end->setTimeZone('Europe/Kyiv');

            $googleEvent->setStart($start);
            $googleEvent->setEnd($end);

            $service->events->update('primary', $googleEvent->getId(), $googleEvent);

            return true;

        } catch (\Throwable $e) {
            return false;
        }
    }

    // ❌ DELETE EVENT
    public function deleteEvent($user, EventModel $event)
    {
        try {
            if (! $event->google_event_id) {
                return true;
            }

            $client = $this->getClient($user);
            if (! $client) {
                return false;
            }

            $service = new Calendar($client);

            try {
                $service->events->delete('primary', $event->google_event_id);
            } catch (\Throwable $e) {
                // якщо вже видалений — ігноруємо
            }

            return true;

        } catch (\Throwable $e) {
            return false;
        }
    }
}
