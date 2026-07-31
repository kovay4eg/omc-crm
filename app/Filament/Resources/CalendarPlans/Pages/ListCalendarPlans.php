<?php

namespace App\Filament\Resources\CalendarPlans\Pages;

use App\Filament\Resources\CalendarPlans\CalendarPlanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCalendarPlans extends ListRecords
{
    protected static string $resource = CalendarPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
