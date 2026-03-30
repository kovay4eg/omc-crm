<?php

namespace App\Filament\Resources\SystemLogs\SystemLogResource\Pages;

use App\Filament\Resources\SystemLogs\SystemLogResource;
use Filament\Resources\Pages\ListRecords;

class ListSystemLogs extends ListRecords
{
    protected static string $resource = SystemLogResource::class;
}