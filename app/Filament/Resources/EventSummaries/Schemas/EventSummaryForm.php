<?php

namespace App\Filament\Resources\EventSummaries\Schemas;

use App\Models\Event;
use App\Models\EventSummary;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EventSummaryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Дані заходу')
                    ->description('Ці поля беруться з івенту та не редагуються на сторінці підсумку.')
                    ->schema([
                        Placeholder::make('event_title')
                            ->label('Назва заходу')
                            ->content(fn (?Event $record): string => $record?->title ?? '—'),

                        Placeholder::make('event_date')
                            ->label('Дата проведення')
                            ->content(fn (?Event $record): string => $record?->event_date?->format('d.m.Y H:i') ?? '—'),

                        Placeholder::make('event_description')
                            ->label('Опис заходу')
                            ->content(fn (?Event $record): string => $record?->description ?? '—')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('Підсумок заходу')
                    ->description('Чернетка зберігається лише в адмінці. На сайт потрапляють тільки опубліковані підсумки.')
                    ->relationship('summary')
                    ->schema([
                        Textarea::make('summary')
                            ->label('Текст підсумку')
                            ->rows(7)
                            ->required()
                            ->columnSpanFull(),

                        Select::make('status')
                            ->label('Стан підсумку')
                            ->options([
                                EventSummary::STATUS_DRAFT => 'Чернетка — не показувати на сайті',
                                EventSummary::STATUS_PUBLISHED => 'Опубліковано — показувати на сайті',
                            ])
                            ->default(EventSummary::STATUS_DRAFT)
                            ->required(),

                        TextInput::make('smm_title')
                            ->label('Заголовок для соцмереж')
                            ->maxLength(255)
                            ->helperText('Необов’язково. За замовчуванням буде назва заходу.'),

                        Textarea::make('smm_description')
                            ->label('Короткий опис для соцмереж')
                            ->rows(3)
                            ->maxLength(200)
                            ->helperText('Необов’язково. За замовчуванням буде текст підсумку.')
                            ->columnSpanFull(),

                        FileUpload::make('smm_image')
                            ->label('Обкладинка для посилання')
                            ->image()
                            ->disk('public')
                            ->directory('smm/summaries')
                            ->helperText('Рекомендований розмір: 1200 × 630 px. Якщо не додавати, буде використано фото заходу.')
                            ->columnSpanFull(),

                    ])
                    ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                        $data['user_id'] = auth()->id();

                        return $data;
                    })
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('Фотогалерея')
                    ->description('Необов’язково. Можна вибрати одразу кілька фото — після збереження вони з’являться у галереї.')
                    ->schema([
                        FileUpload::make('new_images')
                            ->label('Фото')
                            ->image()
                            ->multiple()
                            ->reorderable()
                            ->appendFiles()
                            ->disk('public')
                            ->directory('event-summaries')
                            ->maxFiles(30)
                            ->helperText('Оберіть усі фото в одному вікні вибору файлів.')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
