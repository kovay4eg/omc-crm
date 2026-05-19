<?php

namespace App\Filament\Resources\Statutes\Pages;

use App\Filament\Resources\Statutes\StatuteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStatutes extends ListRecords
{
    protected static string $resource = StatuteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
