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

class EditEvent extends EditRecord
{
    protected static string $resource = EventResource::class;

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
     * 🔥 HEADER ACTIONS
     */
    protected function getHeaderActions(): array
    {
        return [

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

                    $formData = $this->form->getState();

                    $formData['event_date'] = $data['new_date'];

                    $this->form->fill($formData);

                    // 🔥 тригер для JS
                    $this->dispatchBrowserEvent('form-changed');
                }),
        ];
    }
}