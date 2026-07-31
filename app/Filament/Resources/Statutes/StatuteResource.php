<?php

namespace App\Filament\Resources\Statutes;

use App\Filament\Resources\Statutes\Pages\CreateStatute;
use App\Filament\Resources\Statutes\Pages\EditStatute;
use App\Filament\Resources\Statutes\Pages\ListStatutes;
use App\Models\Statute;

use BackedEnum;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;

use Filament\Resources\Resource;

use Filament\Schemas\Schema;

use Filament\Support\Icons\Heroicon;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StatuteResource extends Resource
{
    protected static ?string $model = Statute::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|\UnitEnum|null $navigationGroup = 'Контент сайту';

    protected static ?string $navigationLabel = 'Статут';

    protected static ?string $pluralModelLabel = 'Статути';

    protected static ?string $modelLabel = 'Статут';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([

                TextInput::make('title')
                    ->label('Назва статуту')
                    ->required()
                    ->maxLength(255),

                FileUpload::make('file')
                    ->label('Файл статуту')
                    ->disk('public')
                    ->visibility('public')
                    ->directory('statutes')
                    ->acceptedFileTypes([
                        'application/pdf',
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    ])
                    ->downloadable()
                    ->openable()
                    ->previewable(false)
                    ->required(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('title')
                    ->label('Назва')
                    ->searchable(),

                TextColumn::make('file')
                    ->label('Файл')
                    ->formatStateUsing(fn ($state) => basename($state)),

                TextColumn::make('created_at')
                    ->label('Створено')
                    ->dateTime('d.m.Y H:i'),

            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStatutes::route('/'),
            'create' => CreateStatute::route('/create'),
            'edit' => EditStatute::route('/{record}/edit'),
        ];
    }
}
