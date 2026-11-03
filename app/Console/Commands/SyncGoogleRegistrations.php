<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Services\GoogleSheetsRegistrationService;
use Illuminate\Console\Command;
use Throwable;

class SyncGoogleRegistrations extends Command
{
    protected $signature = 'google:sync-registrations {--event= : Синхронізувати лише одну подію}';

    protected $description = 'Імпортує реєстрації учасників із таблиць відповідей Google Forms';

    public function handle(GoogleSheetsRegistrationService $sheets): int
    {
        $events = Event::query()
            ->with('googleSyncUser')
            ->where('registration_type', 'external')
            ->whereNotNull('google_sheet_url')
            ->whereNotNull('google_sync_user_id')
            ->when($this->option('event'), fn ($query, $eventId) => $query->whereKey($eventId))
            ->get();

        $failed = 0;

        foreach ($events as $event) {
            if (! $event->googleSyncUser?->hasGoogleConnection()) {
                $this->warn("Подія {$event->id}: Google не підключено.");
                $failed++;

                continue;
            }

            try {
                $result = $sheets->sync($event->googleSyncUser, $event);
                $this->info(
                    "Подія {$event->id}: +{$result['imported']}, оновлено {$result['updated']}, пропущено {$result['skipped']}.",
                );
            } catch (Throwable $exception) {
                report($exception);
                $this->error("Подія {$event->id}: синхронізація не вдалася.");
                $failed++;
            }
        }

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
