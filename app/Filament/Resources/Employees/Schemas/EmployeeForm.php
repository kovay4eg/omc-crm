<?php

namespace App\Filament\Resources\Employees\Schemas;

use App\Models\Position;
use App\Models\Department;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class EmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('first_name')
                    ->label('Імʼя')
                    ->required(),

                TextInput::make('last_name')
                    ->label('Прізвище')
                    ->required(),

                TextInput::make('middle_name')
                    ->label('По батькові'),

                Select::make('position_id')
                    ->label('Посада')
                    ->relationship('position', 'name')
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        TextInput::make('name')
                            ->label('Назва посади')
                            ->required(),
                    ])
                    ->required(),

                Select::make('department_id')
                    ->label('Відділ')
                    ->relationship('department', 'name')
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        TextInput::make('name')
                            ->label('Назва відділу')
                            ->required(),
                    ])
                    ->required(),

                TextInput::make('photo')
                    ->label('Фото'),

                TextInput::make('sort')
                    ->numeric()
                    ->default(0)
                    ->hidden(),
            ]);
    }
}