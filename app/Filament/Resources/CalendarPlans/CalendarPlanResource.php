<?php

namespace App\Filament\Resources\CalendarPlans;

use App\Filament\Resources\CalendarPlans\Pages\CreateCalendarPlan;
use App\Filament\Resources\CalendarPlans\Pages\EditCalendarPlan;
use App\Filament\Resources\CalendarPlans\Pages\ListCalendarPlans;
use App\Filament\Resources\CalendarPlans\Schemas\CalendarPlanForm;
use App\Filament\Resources\CalendarPlans\Tables\CalendarPlansTable;
use App\Models\CalendarPlan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CalendarPlanResource extends Resource
{
    protected static ?string $model = CalendarPlan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Календарний план';

    protected static ?string $pluralModelLabel = 'Календарний план';

    protected static ?string $modelLabel = 'План';

    protected static string|\UnitEnum|null $navigationGroup = 'Контент сайту';

    protected static ?int $navigationSort = 7;

    public static function form(Schema $schema): Schema
    {
        return CalendarPlanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CalendarPlansTable::configure($table);
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
            'index' => ListCalendarPlans::route('/'),
            'create' => CreateCalendarPlan::route('/create'),
            'edit' => EditCalendarPlan::route('/{record}/edit'),
        ];
    }
}
