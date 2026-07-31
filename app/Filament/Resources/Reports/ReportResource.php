<?php

namespace App\Filament\Resources\Reports;

use App\Models\Report;

use Filament\Schemas\Schema;

use Filament\Tables\Table;
use Filament\Tables\TableComponent;

use Filament\Resources\Resource;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;

use Filament\Tables\Columns\TextColumn;

use App\Filament\Resources\Reports\Pages;

class ReportResource extends Resource
{
    protected static ?string $model = Report::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|\UnitEnum|null $navigationGroup = 'Контент сайту';

    protected static ?string $navigationLabel = 'Звітність';

    protected static ?string $pluralModelLabel = 'Звітність';

    protected static ?string $modelLabel = 'Звіт';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([

                TextInput::make('title')
                    ->label('Назва звіту')
                    ->required()
                    ->maxLength(255),

                TextInput::make('year')
                    ->label('Рік')
                    ->required()
                    ->numeric(),

                FileUpload::make('file')
                    ->label('Файл звітності')
                    ->disk('public')
                    ->visibility('public')
                    ->directory('reports')
                    ->acceptedFileTypes([
                        'application/pdf',
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'application/vnd.ms-excel',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    ])
                    ->downloadable()
                    ->openable()
                    ->required(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('year')
                    ->label('Рік')
                    ->sortable(),

                TextColumn::make('title')
                    ->label('Назва')
                    ->searchable(),

                TextColumn::make('file')
                    ->label('Файл')
                    ->limit(40),

                TextColumn::make('created_at')
                    ->label('Створено')
                    ->dateTime('d.m.Y H:i'),

            ])
            ->defaultSort('year', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReports::route('/'),
            'create' => Pages\CreateReport::route('/create'),
            'edit' => Pages\EditReport::route('/{record}/edit'),
        ];
    }
}
