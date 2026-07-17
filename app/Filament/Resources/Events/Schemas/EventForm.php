<?php

namespace App\Filament\Resources\Events\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
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
                ->minDate(fn () =>
                    Auth::user()?->getActiveRole() === 'admin'
                        ? null
                        : now()
                )
                ->disabled(function ($operation) {
                    return $operation === 'edit'
                        && Auth::user()?->getActiveRole() !== 'admin';
                })
                ->helperText(function ($operation) {
                    return $operation === 'edit'
                        && Auth::user()?->getActiveRole() !== 'admin'
                        ? 'Зміна дати доступна тільки через кнопку "Перенести івент".'
                        : null;
                }),

            FileUpload::make('image')
                ->label('Фото')
                ->image()
                ->directory('events')
                ->nullable(),

            Toggle::make('has_registration_button')
                ->label('Показувати кнопку "Записатись"')
                ->default(false)
                ->live(),

            Select::make('registration_type')
                ->label('Тип реєстрації')
                ->options([
                    'internal' => 'Форма (імʼя, телефон, email)',
                    'external' => 'Google форма',
                ])
                ->visible(fn ($get) => $get('has_registration_button'))
                ->required(fn ($get) => $get('has_registration_button'))
                ->live(),

            TextInput::make('google_form_url')
                ->label('Посилання на Google форму')
                ->url()
                ->visible(fn ($get) =>
                    $get('has_registration_button')
                    && $get('registration_type') === 'external'
                )
                ->required(fn ($get) =>
                    $get('has_registration_button')
                    && $get('registration_type') === 'external'
                ),

            TextInput::make('max_participants')
                ->label('Макс. кількість учасників')
                ->numeric()
                ->minValue(1)
                ->nullable()
                ->live(),

            Toggle::make('show_available_slots')
                ->label('Показувати кількість місць на сайті')
                ->default(true),

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