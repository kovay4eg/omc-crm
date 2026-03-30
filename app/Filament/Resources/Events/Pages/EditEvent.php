<?php

namespace App\Filament\Resources\Events\Pages;

use App\Enums\EventStatus;
use App\Filament\Resources\Events\EventResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Toggle;
use Illuminate\Support\Facades\Mail;
use App\Mail\EventCancelledNotification;
use App\Models\EventHistory;

class EditEvent extends EditRecord
{
    protected static string $resource = EventResource::class;

    protected array $oldData = [];

    protected function beforeSave(): void
    {
        $this->oldData = $this->record->getOriginal();
    }

    protected function afterSave(): void
    {
        // 🔥 SYSTEM LOG (редагування)
        $changes = [];

        foreach ($this->record->getChanges() as $field => $newValue) {
            $oldValue = $this->oldData[$field] ?? null;

            if ($oldValue != $newValue) {
                $changes[] = "$field: $oldValue → $newValue";
            }
        }

        system_log(
            'update_event',
            'Оновлено івент: ' . $this->record->title .
            ' | Зміни: ' . implode(', ', $changes)
        );
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (auth()->user()?->getActiveRole() !== 'admin') {
            unset($data['event_date']);
        }

        return $data;
    }

    protected function getFormActions(): array
    {
        return [
            Actions\Action::make('save')
                ->label('Зберегти зміни')
                ->submit('save'),

            Actions\Action::make('cancel')
                ->label('Скасувати')
                ->url($this->getResource()::getUrl('index')),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [

            // 🔥 DELETE + SYSTEM LOG
            Actions\DeleteAction::make()
                ->label('Видалити івент')
                ->color('danger')
                ->visible(fn () => auth()->user()?->getActiveRole() === 'admin')

                ->modalHeading(fn () => 'Видалити "' . $this->record->title . '"')
                ->modalDescription('Ви впевнені, що хочете видалити цей івент?')
                ->modalSubmitActionLabel('Видалити')
                ->modalCancelActionLabel('Скасувати')

                ->before(function () {

                    // EventHistory
                    EventHistory::create([
                        'event_id'   => $this->record->id,
                        'user_id'    => auth()->id(),
                        'action'     => 'deleted',
                        'description'=> 'Івент видалено',
                        'old_date'   => $this->record->event_date,
                        'new_date'   => null,
                        'is_public'  => false,
                    ]);

                    // 🔥 SYSTEM LOG
                    system_log(
                        'delete_event',
                        'Видалено івент: ' . $this->record->title
                    );
                }),

            Actions\Action::make('cancel_event')
                ->label('Скасувати івент')
                ->color('danger')
                ->visible(fn () => $this->record->status !== EventStatus::Cancelled)

                ->form([
                    Textarea::make('reason')->label('Причина скасування')->required(),
                    Toggle::make('is_public')->label('Показати причину на сайті'),
                    Toggle::make('notify_users')->label('Сповістити учасників'),
                ])

                ->action(function (array $data) {

                    $event = $this->record;

                    EventHistory::create([
                        'event_id'   => $event->id,
                        'user_id'    => auth()->id(),
                        'action'     => 'cancelled',
                        'description'=> $data['reason'],
                        'old_date'   => $event->event_date,
                        'new_date'   => null,
                        'is_public'  => $data['is_public'] ?? false,
                    ]);

                    $event->update([
                        'status' => EventStatus::Cancelled,
                        'cancel_reason' => $data['reason'],
                        'cancel_public' => $data['is_public'] ?? false,
                        'cancelled_at' => now(),
                    ]);
                }),

            Actions\Action::make('reschedule_event')
                ->label('Перенести івент')
                ->color('warning')

                ->form([
                    DateTimePicker::make('new_date')->label('Нова дата')->required(),
                    Textarea::make('reason')->label('Причина перенесення')->required(),
                ])

                ->action(function (array $data) {

                    $event = $this->record;

                    EventHistory::create([
                        'event_id'   => $event->id,
                        'user_id'    => auth()->id(),
                        'action'     => 'rescheduled',
                        'description'=> $data['reason'],
                        'old_date'   => $event->event_date,
                        'new_date'   => $data['new_date'],
                        'is_public'  => false,
                    ]);

                    $event->update([
                        'event_date' => $data['new_date'],
                    ]);
                }),
        ];
    }
}