<?php

namespace App\Filament\Resources\EventSummaries\Pages;

use App\Filament\Resources\EventSummaries\EventSummaryResource;
use Filament\Resources\Pages\ListRecords;

class ListEventSummaries extends ListRecords
{
    protected static string $resource = EventSummaryResource::class;

    public function getTitle(): string
    {
        return 'Підсумки заходів';
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
