<?php

namespace App\Filament\Resources\EventSummaries\Tables;

use App\Models\Event;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EventSummariesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Захід')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('event_date')
                    ->label('Дата проведення')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),

                TextColumn::make('summary_status')
                    ->label('Стан підсумку')
                    ->state(fn (Event $record): string => $record->summary?->status ?? 'missing')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Створено — не опубліковано',
                        'published' => 'Опубліковано',
                        default => 'Підсумок не створено',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'warning',
                        'published' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('summary.author.name')
                    ->label('Автор підсумку')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('summary.updated_at')
                    ->label('Остання зміна')
                    ->dateTime('d.m.Y H:i')
                    ->placeholder('—')
                    ->toggleable(),
            ])
            ->recordActions([
                EditAction::make()
                    ->label(fn (Event $record): string => $record->summary ? 'Редагувати підсумок' : 'Створити підсумок')
                    ->icon(Heroicon::PencilSquare)
                    ->color(fn (Event $record): string => $record->summary ? 'gray' : 'primary'),
            ])
            ->defaultSort('event_date', 'desc');
    }
}
