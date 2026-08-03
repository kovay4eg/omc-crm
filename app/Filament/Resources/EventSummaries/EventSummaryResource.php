<?php

namespace App\Filament\Resources\EventSummaries;

use App\Enums\EventStatus;
use App\Filament\Resources\EventSummaries\Pages\EditEventSummary;
use App\Filament\Resources\EventSummaries\Pages\ListEventSummaries;
use App\Filament\Resources\EventSummaries\Schemas\EventSummaryForm;
use App\Filament\Resources\EventSummaries\Tables\EventSummariesTable;
use App\Models\Event;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class EventSummaryResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = 'Підсумки заходів';

    protected static ?string $pluralLabel = 'Підсумки заходів';

    protected static ?string $modelLabel = 'Підсумок заходу';

    protected static ?string $recordTitleAttribute = 'title';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'summary.author',
            ])
            ->whereDate('event_date', '<', today())
            ->whereIn('status', [
                EventStatus::Published->value,
                EventStatus::Rescheduled->value,
            ])
            ->orderByDesc('event_date');
    }

    public static function canViewAny(): bool
    {
        return in_array(auth()->user()?->getActiveRole(), [
            'admin',
            'editor',
            'content',
        ]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return in_array(auth()->user()?->getActiveRole(), [
            'admin',
            'editor',
            'content',
        ]);
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return EventSummaryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EventSummariesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEventSummaries::route('/'),
            'edit' => EditEventSummary::route('/{record}/edit'),
        ];
    }
}
