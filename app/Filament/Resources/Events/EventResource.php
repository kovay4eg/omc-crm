<?php

namespace App\Filament\Resources\Events;

use App\Filament\Resources\Events\Schemas\EventForm;
use App\Filament\Resources\Events\Tables\EventsTable;
use App\Filament\Resources\Events\RelationManagers\RegistrationsRelationManager;
use App\Filament\Resources\Events\RelationManagers\HistoriesRelationManager; 
use App\Models\Event;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class EventResource extends Resource
{
    protected static ?string $navigationLabel = 'Івенти';

    protected static ?string $pluralLabel = 'Івенти';
        
    protected static ?string $modelLabel = 'Івент';
    
    protected static ?string $model = Event::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-m-calendar';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return EventForm::schema($schema);
    }

    public static function table(Table $table): Table
    {
        return EventsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RegistrationsRelationManager::class,
            HistoriesRelationManager::class, 
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\Events\Pages\ListEvents::route('/'),
            'create' => \App\Filament\Resources\Events\Pages\CreateEvent::route('/create'),
            'edit' => \App\Filament\Resources\Events\Pages\EditEvent::route('/{record}/edit'),
        ];
    }
}