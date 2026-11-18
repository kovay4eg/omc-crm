<?php

namespace App\Filament\Resources\Events\Pages;

use App\Filament\Resources\Events\EventResource;
use App\Models\EventHistory;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateEvent extends CreateRecord
{
    protected static string $resource = EventResource::class;

    protected static bool $canCreateAnother = false;

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->submit(null)
            ->action('create');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResourceUrl('index');
    }

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
            'Створено івент: '.$this->record->title
        );

    }
}
