<?php

namespace App\Filament\Resources\EventSummaries\Schemas;

use App\Models\Event;
use App\Models\EventSummary;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
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

                        Repeater::make('images')
                            ->label('Фотогалерея')
                            ->relationship()
                            ->orderColumn('sort_order')
                            ->schema([
                                FileUpload::make('image')
                                    ->label('Фото')
                                    ->image()
                                    ->disk('public')
                                    ->directory('event-summaries')
                                    ->imagePreviewHeight('140')
                                    ->required(),

                                TextInput::make('alt_text')
                                    ->label('Короткий опис фото')
                                    ->maxLength(255),
                            ])
                            ->columns(2)
                            ->addActionLabel('Додати фото')
                            ->reorderableWithButtons()
                            ->collapsible()
                            ->columnSpanFull(),
                    ])
                    ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                        $data['user_id'] = auth()->id();

                        return $data;
                    })
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('Швидке додавання фото')
                    ->description('Можна вибрати одразу кілька фото. Після збереження вони з’являться у фотогалереї нижче.')
                    ->schema([
                        FileUpload::make('new_images')
                            ->label('Нові фото')
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
