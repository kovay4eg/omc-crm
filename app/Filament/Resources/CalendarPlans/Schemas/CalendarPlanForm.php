<?php

namespace App\Filament\Resources\CalendarPlans\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CalendarPlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                TextInput::make('title')
                    ->label('Назва документа')
                    ->required()
                    ->maxLength(255),

                TextInput::make('year')
                    ->label('Рік')
                    ->required()
                    ->numeric(),

                FileUpload::make('file')
                    ->label('Файл календарного плану')
                    ->disk('public')
                    ->visibility('public')
                    ->directory('calendar-plans')
                    ->acceptedFileTypes([
                        'application/pdf',
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'application/vnd.ms-excel',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    ])
                    ->required(),
            ]);
    }
}
