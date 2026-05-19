<?php

namespace App\Filament\Resources\Statutes\Pages;

use App\Filament\Resources\Statutes\StatuteResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditStatute extends EditRecord
{
    protected static string $resource = StatuteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
