<?php

namespace App\Filament\Resources\Events\Pages;

use App\Enums\EventStatus;
use App\Filament\Resources\Events\EventResource;
use App\Models\EventHistory;
use App\Services\GoogleCalendarService;
use BackedEnum;
use DateTimeInterface;
use Filament\Actions;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

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
        $changes = [];

        foreach ($this->record->getChanges() as $field => $newValue) {
            $oldValue = $this->oldData[$field] ?? null;

            if ($oldValue != $newValue) {
                $changes[] = $field
                    . ': '
                    . $this->formatChangeValue($oldValue)
                    . ' → '
                    . $this->formatChangeValue($newValue);
            }
        }

        system_log(
            'update_event',
            'Оновлено івент: ' . $this->record->title .
            ' | Зміни: ' . implode(', ', $changes)
        );

        $success = app(GoogleCalendarService::class)
            ->updateEvent(auth()->user(), $this->record);

        if (!$success) {
            Notification::make()
                ->title('Google не відповів')
                ->body('Івент оновлено, але не синхронізовано з Google')
                ->warning()
                ->send();
        }
    }

    protected function formatChangeValue(mixed $value): string
    {
        if ($value instanceof BackedEnum) {
            return (string) $value->value;
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format('d.m.Y H:i');
        }

        if ($value === null) {
            return 'null';
        }

        return (string) $value;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (auth()->user()?->getActiveRole() !== 'admin') {
            unset($data['event_date']);
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Видалити івент')
                ->color('danger')
                ->visible(fn () => auth()->user()?->getActiveRole() === 'admin'),

            Actions\Action::make('cancel_event')
                ->label('Скасувати івент')
                ->color('danger')
                ->visible(fn () => $this->record->status !== EventStatus::Cancelled)
                ->form([
                    Textarea::make('reason')
                        ->label('Причина скасування')
                        ->required(),

                    Toggle::make('cancel_public')
                        ->label('Показувати причину на сайті'),

                    Toggle::make('notify_users')
                        ->label('Сповістити учасників'),
                ])
                ->action(function (array $data) {
                    $event = $this->record;

                    EventHistory::create([
                        'event_id' => $event->id,
                        'user_id' => auth()->id(),
                        'action' => 'cancelled',
                        'description' => $data['reason'],
                        'old_date' => $event->event_date,
                        'new_date' => null,
                        'is_public' => $data['cancel_public'] ?? false,
                    ]);

                    $event->update([
                        'status' => EventStatus::Cancelled,
                        'cancel_reason' => $data['reason'],
                        'cancel_public' => $data['cancel_public'] ?? false,
                        'cancelled_at' => now(),
                    ]);

                    $success = app(GoogleCalendarService::class)
                        ->updateEvent(auth()->user(), $event);

                    if (!$success) {
                        Notification::make()
                            ->title('Google не відповів')
                            ->warning()
                            ->send();
                    }
                }),

            Actions\Action::make('reschedule_event')
                ->label('Перенести івент')
                ->color('warning')
                ->visible(fn () => $this->record->status !== EventStatus::Cancelled)
                ->form([
                    DateTimePicker::make('new_date')
                        ->label('Нова дата')
                        ->required()
                        ->seconds(false)
                        ->default(fn () => $this->record->event_date),

                    Textarea::make('reason')
                        ->label('Причина перенесення')
                        ->required(),

                    Toggle::make('reschedule_public')
                        ->label('Показувати причину на сайті'),
                ])
                ->action(function (array $data) {
                    $event = $this->record;
                    $oldEventDate = $event->event_date;

                    EventHistory::create([
                        'event_id' => $event->id,
                        'user_id' => auth()->id(),
                        'action' => 'rescheduled',
                        'description' => $data['reason'],
                        'old_date' => $oldEventDate,
                        'new_date' => $data['new_date'],
                        'is_public' => $data['reschedule_public'] ?? false,
                    ]);

                    $event->update([
                        'event_date' => $data['new_date'],
                        'status' => EventStatus::Rescheduled,
                        'old_event_date' => $oldEventDate,
                        'rescheduled_at' => now(),
                        'reschedule_reason' => $data['reason'],
                        'reschedule_public' => $data['reschedule_public'] ?? false,
                    ]);

                    $this->record->refresh();
                    $this->fillForm();

                    $success = app(GoogleCalendarService::class)
                        ->updateEvent(auth()->user(), $event);

                    if (!$success) {
                        Notification::make()
                            ->title('Google не відповідає')
                            ->warning()
                            ->send();
                    }
                }),
        ];
    }
}