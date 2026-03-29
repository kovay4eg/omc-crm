<?php

namespace App\Filament\Resources\Events\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Validation\ValidationException;
use App\Enums\EventStatus;

class RegistrationsRelationManager extends RelationManager
{
    /**
     * Назва зв’язку в моделі Event
     */
    protected static string $relationship = 'registrations';

    /**
     * Заголовок блоку в адмінці
     */
    protected static ?string $title = 'Реєстрації';

    /**
     * Форма створення / редагування реєстрації
     */
    public function form(Schema $schema): Schema
    {
        return $schema->schema([

            // Імʼя користувача
            TextInput::make('name')
                ->label('Імʼя')
                ->required(),

            // Email
            TextInput::make('email')
                ->label('Email')
                ->email()
                ->required(),

            // Телефон
            TextInput::make('phone')
                ->label('Телефон')
                ->required(),
        ]);
    }

    /**
     * Таблиця реєстрацій
     */
    public function table(Table $table): Table
    {
        return $table

            /**
             * Колонки таблиці
             */
            ->columns([
                TextColumn::make('name')
                    ->label('Імʼя')
                    ->searchable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),

                TextColumn::make('phone')
                    ->label('Телефон'),

                TextColumn::make('created_at')
                    ->label('Дата')
                    ->dateTime('d.m.Y H:i'),
            ])

            /**
             * Кнопки зверху (створення)
             */
            ->headerActions([

                \Filament\Actions\CreateAction::make()
                    ->label('Нова реєстрація')

                    // ❌ НЕ показуємо кнопку якщо івент скасований
                    ->visible(fn ($livewire) =>
                        $livewire->ownerRecord->status !== EventStatus::Cancelled
                    )

                    // ❌ Захист навіть якщо хтось обійде UI
                    ->mutateFormDataUsing(function (array $data, $livewire) {

                        $event = $livewire->ownerRecord;

                        // Якщо івент скасований — забороняємо створення
                        if ($event->status === EventStatus::Cancelled) {
                            throw ValidationException::withMessages([
                                'name' => 'Цей захід скасовано',
                            ]);
                        }

                        return $data;
                    }),
            ])

            /**
             * Дії для кожного запису
             */
            ->actions([

                // ✏️ Редагування
                \Filament\Actions\EditAction::make(),

                // 🗑 Видалення
                \Filament\Actions\DeleteAction::make(),
            ]);
    }
}