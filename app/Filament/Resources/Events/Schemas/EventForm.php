<?php

namespace App\Filament\Resources\Events\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class EventForm
{
    public static function schema(Schema $schema): Schema
    {
        return $schema->components([

            TextInput::make('title')
                ->label('Назва')
                ->required()
                ->maxLength(255),

            TextInput::make('notify_email')
                ->label('Email для сповіщень про реєстрацію')
                ->email()
                ->nullable(),

            Textarea::make('description')
                ->label('Опис')
                ->required()
                ->rows(4),

            DateTimePicker::make('event_date')
                ->label('Дата події')
                ->required()
                ->seconds(false)
                ->native(true)
                ->minDate(fn () => Auth::user()->isAdmin() ? null : now()),

            FileUpload::make('image')
                ->label('Фото')
                ->image()
                ->directory('events')
                ->nullable(),

            Toggle::make('has_registration')
                ->label('Показувати кнопку "Записатись"')
                ->default(false)
                ->reactive(),

            // 🔥 ВАЖЛИВО: додали ->live()
            Select::make('registration_type')
                ->label('Тип реєстрації')
                ->options([
                    'internal' => 'Форма (імʼя, телефон, email)',
                    'external' => 'Google форма',
                ])
                ->live() // 👈 ОЦЕ ФІКС
                ->visible(fn ($get) => $get('has_registration'))
                ->required(fn ($get) => $get('has_registration')),

            TextInput::make('external_link')
                ->label('Посилання на Google форму')
                ->url()
                ->visible(fn ($get) => $get('registration_type') === 'external'),

            TextInput::make('max_participants')
                ->label('Макс. кількість учасників')
                ->numeric()
                ->minValue(1)
                ->nullable(),

            Select::make('status')
                ->label('Статус')
                ->options([
                    'draft' => 'Чернетка',
                    'published' => 'Опубліковано',
                ])
                ->default('draft')
                ->required(),

        ]);
    }
}