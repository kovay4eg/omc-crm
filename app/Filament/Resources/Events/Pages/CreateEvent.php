<?php

namespace App\Filament\Resources\Events\Pages;

use App\Filament\Resources\Events\EventResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use App\Models\EventHistory;
use App\Services\GoogleCalendarService;
use Filament\Notifications\Notification;

class CreateEvent extends CreateRecord
{
    protected static string $resource = EventResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();
        return $data;
    }

    protected function afterCreate(): void
    {
        // ІСТОРІЯ
        EventHistory::create([
            'event_id' => $this->record->id,
            'user_id' => Auth::id(),
            'action' => 'created',
            'description' => 'Створення івенту',
            'old_date' => null,
            'new_date' => $this->record->event_date,
            'is_public' => false,
        ]);

        // SYSTEM LOG
        system_log(
            'create_event',
            'Створено івент: ' . $this->record->title
        );

        // GOOGLE SYNC
        $success = app(GoogleCalendarService::class)
            ->createEvent(Auth::user(), $this->record);

        // ПОПАП ЯКЩО GOOGLE НЕ СПРАЦЮВАВ
        if (!$success) {
            Notification::make()
                ->title('Google не відповів')
                ->body('Івент створено, але не синхронізовано з Google')
                ->warning()
                ->send();
        }
    }
}