<?php

namespace App\Filament\Resources\Events\Tables;

use App\Enums\EventStatus;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('title')
                    ->label('Назва')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('event_date')
                    ->label('Дата')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),

                /**
                 * 🔥 СТАТУС (з підтримкою перенесення)
                 */
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()

                    ->formatStateUsing(function ($state, $record) {

                        // 🔥 якщо вже enum — використовуємо напряму
                        $status = $state instanceof EventStatus
                            ? $state
                            : EventStatus::from($state);

                        // ❌ скасовано
                        if ($status === EventStatus::Cancelled) {
                            return 'Скасовано';
                        }

                        // 🔥 перенесено
                        if ($record->rescheduled_at && $record->old_event_date) {
                            return 'Перенесено: '
                                .$record->old_event_date->format('d.m')
                                .' → '
                                .$record->event_date->format('d.m');
                        }

                        return $status->label();
                    })

                    ->color(function ($state, $record) {

                        $status = $state instanceof EventStatus
                            ? $state
                            : EventStatus::from($state);

                        if ($status === EventStatus::Cancelled) {
                            return 'danger';
                        }

                        if ($record->rescheduled_at) {
                            return 'warning';
                        }

                        return $status->color();
                    }),

                TextColumn::make('registrations_count')
                    ->label('Учасники')
                    ->counts('registrations')
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Автор')
                    ->default('-'),

            ]);
    }
}
