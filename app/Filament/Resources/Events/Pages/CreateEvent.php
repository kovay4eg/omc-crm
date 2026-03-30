<?php

namespace App\Filament\Resources\Events\Pages;

use App\Filament\Resources\Events\EventResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use App\Models\EventHistory;

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
    }
}