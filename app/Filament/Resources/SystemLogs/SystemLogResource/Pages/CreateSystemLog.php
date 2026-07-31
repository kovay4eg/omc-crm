<?php

namespace App\Filament\Resources\SystemLogs\Pages;

use App\Filament\Resources\SystemLogs\SystemLogResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSystemLog extends CreateRecord
{
    protected static string $resource = SystemLogResource::class;
}
