<?php

namespace App\Filament\Resources\CalendarPlans\Pages;

use App\Filament\Resources\CalendarPlans\CalendarPlanResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCalendarPlan extends EditRecord
{
    protected static string $resource = CalendarPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
