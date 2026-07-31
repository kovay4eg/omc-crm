<?php

namespace App\Filament\Resources\Statutes;

use App\Filament\Resources\Statutes\Pages\CreateStatute;
use App\Filament\Resources\Statutes\Pages\EditStatute;
use App\Filament\Resources\Statutes\Pages\ListStatutes;
use App\Filament\Resources\Statutes\Schemas\StatuteForm;
use App\Filament\Resources\Statutes\Tables\StatutesTable;
use App\Models\Statute;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class StatuteResource extends Resource
{
    protected static ?string $model = Statute::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = 'Статут';

    protected static ?string $pluralModelLabel = 'Статут';

    protected static ?string $modelLabel = 'Статут';

    protected static ?string $navigationGroup = 'Контент сайту';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return StatuteForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StatutesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
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