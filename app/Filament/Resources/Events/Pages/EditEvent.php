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

    /**
     * 🔒 БЛОКУЄМО зміну дати НЕ адмінам (з урахуванням preview)
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (auth()->user()?->getActiveRole() !== 'admin') {
            unset($data['event_date']);
        }

        return $data;
    }

    /**
     * 🔥 SAVE / CANCEL
     */
    protected function getFormActions(): array
    {
        return [
            Actions\Action::make('save')
                ->label('Зберегти зміни')
                ->submit('save')
                ->extraAttributes([
                    'id' => 'save-btn',
                    'class' => 'transition-all duration-300'
                ]),

            Actions\Action::make('cancel')
                ->label('Скасувати')
                ->url($this->getResource()::getUrl('index')),
        ];
    }

    /**
     *  HEADER ACTIONS
     */
    protected function getHeaderActions(): array
    {
        return [

            /**
             * 🗑 ВИДАЛЕННЯ ІВЕНТУ (ТІЛЬКИ АДМІН)
             */
            Actions\DeleteAction::make()
            ->label('Видалити івент')
            ->color('danger')
            ->visible(fn () => auth()->user()?->getActiveRole() === 'admin')

            
            ->modalHeading(fn () => 'Видалити "' . $this->record->title . '"')
            ->modalDescription('Ви впевнені, що хочете видалити цей івент?')
            ->modalSubmitActionLabel('Видалити')
            ->modalCancelActionLabel('Скасувати')

    ->before(function () {
        \App\Models\EventHistory::create([
            'event_id'   => $this->record->id,
            'user_id'    => auth()->id(),
            'action'     => 'deleted',
            'description'=> 'Івент видалено',
            'old_date'   => $this->record->event_date,
            'new_date'   => null,
            'is_public'  => false,
        ]);
    }),

            /**
             * ❌ СКАСУВАТИ
             */
            Actions\Action::make('cancel_event')
                ->label('Скасувати івент')
                ->color('danger')
                ->visible(fn () => $this->record->status !== EventStatus::Cancelled)

                ->form([
                    Textarea::make('reason')
                        ->label('Причина скасування')
                        ->required(),

                    Toggle::make('is_public')
                        ->label('Показати причину на сайті'),

                    Toggle::make('notify_users')
                        ->label('Сповістити учасників'),
                ])

                ->action(function (array $data) {

                    $event = $this->record;

                    if ($event->status === EventStatus::Cancelled) {
                        return;
                    }

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

                    if (!empty($data['notify_users']) && $event->registration_type === 'form') {
                        foreach ($event->registrations as $registration) {
                            if ($registration->email) {
                                Mail::to($registration->email)
                                    ->send(new EventCancelledNotification($registration, $event));
                            }
                        }
                    }
                }),

            /**
             * 🔁 ПЕРЕНЕСЕННЯ
             */
            Actions\Action::make('reschedule_event')
                ->label('Перенести івент')
                ->color('warning')
                ->icon('heroicon-o-calendar-days')

                ->form([
                    DateTimePicker::make('new_date')
                        ->label('Нова дата')
                        ->required()
                        ->default(fn () => $this->record->event_date)
                        ->displayFormat('d.m.Y H:i')
                        ->seconds(false),

                    Textarea::make('reason')
                        ->label('Причина перенесення')
                        ->required(),

                    Toggle::make('is_public')
                        ->label('Показати причину на сайті'),

                    Toggle::make('notify_users')
                        ->label('Сповістити учасників'),
                ])

                ->modalSubmitActionLabel('Перенести')
                ->modalCancelActionLabel('Скасувати')

                ->action(function (array $data) {

                    $event = $this->record;

                    $oldDate = $event->event_date;

                    EventHistory::create([
                        'event_id'   => $event->id,
                        'user_id'    => auth()->id(),
                        'action'     => 'rescheduled',
                        'description'=> $data['reason'],
                        'old_date'   => $oldDate,
                        'new_date'   => $data['new_date'],
                        'is_public'  => $data['is_public'] ?? false,
                    ]);

                    $event->update([
                        'event_date' => $data['new_date'],
                    ]);

                    $this->form->fill([
                        ...$this->form->getState(),
                        'event_date' => $data['new_date'],
                    ]);

                    $this->dispatch('form-changed');
                }),
        ];
    }
}