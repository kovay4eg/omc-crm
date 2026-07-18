<?php

namespace App\Filament\Resources\EventSummaries\Pages;

use App\Filament\Resources\EventSummaries\EventSummaryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEventSummary extends CreateRecord
{
    protected static string $resource = EventSummaryResource::class;
}
