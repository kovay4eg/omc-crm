<?php

namespace App\Filament\Resources\Employees\Tables;

use App\Models\Department;
use App\Models\Position;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EmployeesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->reorderable('sort')
            ->defaultSort('sort')

            ->columns([
                TextColumn::make('last_name')
                    ->label('Прізвище')
                    ->searchable(),

                TextColumn::make('first_name')
                    ->label('Імʼя')
                    ->searchable(),

                TextColumn::make('middle_name')
                    ->label('По батькові')
                    ->searchable(),

                TextColumn::make('position.name')
                    ->label('Посада')
                    ->searchable(),

                TextColumn::make('department.name')
                    ->label('Відділ')
                    ->searchable(),
            ])

            ->headerActions([
                Action::make('manage_departments')
                    ->label('Редагувати відділи')
                    ->form([
                        Repeater::make('departments')
                            ->label('Список відділів')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Назва')
                                    ->required(),
                            ])
                            ->default(fn () => Department::all()->toArray())
                            ->addActionLabel('Додати відділ'),
                    ])
                    ->action(function (array $data) {
                        Department::query()->delete();

                        foreach ($data['departments'] as $item) {
                            Department::create([
                                'name' => $item['name'],
                            ]);
                        }
                    }),

                Action::make('manage_positions')
                    ->label('Редагувати посади')
                    ->form([
                        Repeater::make('positions')
                            ->label('Список посад')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Назва')
                                    ->required(),
                            ])
                            ->default(fn () => Position::all()->toArray())
                            ->addActionLabel('Додати посаду'),
                    ])
                    ->action(function (array $data) {
                        Position::query()->delete();

                        foreach ($data['positions'] as $item) {
                            Position::create([
                                'name' => $item['name'],
                            ]);
                        }
                    }),
            ])

            ->recordActions([
                EditAction::make(),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}