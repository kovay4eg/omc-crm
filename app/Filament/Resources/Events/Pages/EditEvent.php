<?php

namespace App\Filament\Resources\Events\Pages;

use App\Enums\EventStatus;
use App\Filament\Resources\Events\EventResource;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Pages\EditRecord;

class EditEvent extends EditRecord
{
    protected static string $resource = EventResource::class;

    /**
     * 🔹 КНОПКИ УГОРІ (СКАСУВАТИ + ПЕРЕНЕСТИ)
     */
    protected function getHeaderActions(): array
    {
        return [

            /**
             * ❌ СКАСУВАТИ ІВЕНТ
             */
            Action::make('cancel_event')
                ->label('Скасувати івент')
                ->color('danger')

                ->visible(fn () => $this->record->status !== EventStatus::Cancelled)

                ->form([
                    Textarea::make('reason')
                        ->label('Причина скасування')
                        ->required(),

                    Toggle::make('is_public')
                        ->label('Показати причину на сайті')
                        ->default(false),

                    Toggle::make('notify_users')
                        ->label('Сповістити учасників')
                        ->default(false),
                ])

                ->action(function (array $data) {

                    $this->record->update([
                        'status' => EventStatus::Cancelled,
                        'cancel_reason' => $data['reason'],
                        'cancel_public' => $data['is_public'] ?? false,
                        'cancelled_at' => now(),
                    ]);

                    // 📧 Email
                    if (!empty($data['notify_users'])) {
                        foreach ($this->record->registrations as $registration) {
                            if ($registration->email) {
                                \Mail::to($registration->email)
                                    ->send(new \App\Mail\EventCancelledNotification(
                                        $registration,
                                        $this->record
                                    ));
                            }
                        }
                    }

                    // 🔄 оновлення форми
                    $this->record->refresh();

                    $this->form->fill([
                        'title' => $this->record->title,
                        'description' => $this->record->description,
                        'event_date' => $this->record->event_date,
                        'status' => $this->record->status->value,
                        'notify_email' => $this->record->notify_email,
                        'has_registration_button' => $this->record->has_registration_button,
                        'registration_type' => $this->record->registration_type,
                        'max_participants' => $this->record->max_participants,
                    ]);
                }),

            /**
             * 🔄 ПЕРЕНЕСТИ ІВЕНТ
             */
            Action::make('reschedule_event')
                ->label('Перенести івент')
                ->color('warning')
                ->icon('heroicon-o-calendar-days')

                ->visible(fn () => $this->record->status !== EventStatus::Cancelled)

                ->form([
                    DateTimePicker::make('new_date')
                        ->label('Нова дата')
                        ->seconds(false)
                        ->native(false)
                        ->displayFormat('d.m.Y H:i')
                        ->format('Y-m-d H:i:s')
                        ->locale('uk')
                        ->required(),

                    Textarea::make('reason')
                        ->label('Причина перенесення')
                        ->required(),

                    Toggle::make('is_public')
                        ->label('Показати причину на сайті')
                        ->default(false),

                    Toggle::make('notify_users')
                        ->label('Сповістити учасників')
                        ->default(false),
                ])

                ->action(function (array $data) {

                    /**
                     * 🔥 ЗБЕРІГАЄМО СТАРУ ДАТУ
                     */
                    $oldDate = $this->record->event_date;

                    /**
                     * 🔥 ОНОВЛЮЄМО ІВЕНТ (БЕЗ ЗМІНИ СТАТУСУ)
                     */
                    $this->record->update([
                        'event_date' => $data['new_date'],
                        'old_event_date' => $oldDate,
                        'reschedule_reason' => $data['reason'],
                        'reschedule_public' => $data['is_public'] ?? false,
                        'rescheduled_at' => now(),
                    ]);

                    // 📧 Email
                    if (!empty($data['notify_users'])) {
                        foreach ($this->record->registrations as $registration) {
                            if ($registration->email) {
                                \Mail::to($registration->email)
                                    ->send(new \App\Mail\EventRescheduledNotification(
                                        $registration,
                                        $this->record
                                    ));
                            }
                        }
                    }

                    /**
                     * 🔄 ОНОВЛЕННЯ ФОРМИ (щоб не ламалась)
                     */
                    $this->record->refresh();

                    $this->form->fill([
                        'title' => $this->record->title,
                        'description' => $this->record->description,
                        'event_date' => $this->record->event_date,
                        'status' => $this->record->status->value,
                        'notify_email' => $this->record->notify_email,
                        'has_registration_button' => $this->record->has_registration_button,
                        'registration_type' => $this->record->registration_type,
                        'max_participants' => $this->record->max_participants,
                    ]);
                }),

        ];
    }
}