<?php

namespace App\Filament\Resources\Events\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class HistoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'histories';

    protected static ?string $title = 'Історія змін';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('user'))

            ->columns([

                TextColumn::make('action')
                    ->label('Дія')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'rescheduled' => 'Перенесено',
                        'cancelled' => 'Скасовано',
                        'created' => 'Створено',
                        default => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'rescheduled' => 'warning',
                        'cancelled' => 'danger',
                        'created' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('user.name')
                    ->label('Хто')
                    ->default('—'),

                TextColumn::make('old_date')
                    ->label('Було')
                    ->dateTime('d.m.Y H:i'),

                TextColumn::make('new_date')
                    ->label('Стало')
                    ->dateTime('d.m.Y H:i'),

                TextColumn::make('description')
                    ->label('Причина')
                    ->limit(50),

                TextColumn::make('created_at')
                    ->label('Коли')
                    ->since(),
            ])
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
