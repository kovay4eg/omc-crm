<?php

namespace App\Filament\Resources\Events\Schemas;

use App\Support\MediaStorage;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
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
                ->disk('public')
                ->visibility('public')
                ->directory('events')
                ->fetchFileInformation(false)
                ->getUploadedFileUsing(static function (FileUpload $component, string $file, string|array|null $storedFileNames): ?array {
                    $record = $component->getRecord();

                    return MediaStorage::uploadedFileDetails(
                        $file,
                        $storedFileNames,
                        $record ? route('events.image', ['event' => $record]) : null,
                    );
                })
                ->nullable(),

            Section::make('Поширення у соціальних мережах')
                ->description('Необов’язково. Якщо залишити поля порожніми, для прев’ю буде використано назву, опис і фото заходу.')
                ->schema([
                    TextInput::make('smm_title')
                        ->label('Заголовок для соцмереж')
                        ->maxLength(255)
                        ->helperText('Рекомендовано до 70 символів.'),

                    Textarea::make('smm_description')
                        ->label('Короткий опис для соцмереж')
                        ->rows(3)
                        ->maxLength(200)
                        ->helperText('Рекомендовано до 200 символів.')
                        ->columnSpanFull(),

                    FileUpload::make('smm_image')
                        ->label('Обкладинка для посилання')
                        ->image()
                        ->disk('public')
                        ->visibility('public')
                        ->directory('smm/events')
                        ->helperText('Рекомендований розмір: 1200 × 630 px. Якщо не додавати, буде використано афішу заходу.')
                        ->columnSpanFull(),
                ])
                ->columns(2)
                ->columnSpanFull(),

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
                ->visible(fn ($get) =>
                    $get('has_registration_button')
                    && $get('registration_type') !== 'external'
                )
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
