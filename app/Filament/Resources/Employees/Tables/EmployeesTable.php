<?php

namespace App\Filament\Resources\Employees\Tables;

use App\Models\Department;
use App\Models\Position;
use App\Models\SiteSetting;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
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
                TextColumn::make('last_name')->label('Прізвище')->searchable(),
                TextColumn::make('first_name')->label('Імʼя')->searchable(),
                TextColumn::make('middle_name')->label('По батькові')->searchable(),
                TextColumn::make('position.name')->label('Посада')->searchable(),
                TextColumn::make('department.name')->label('Відділ')->searchable(),
            ])

            ->headerActions([

                Action::make('edit_team_banner')
                    ->label('Редагувати загальне фото')
                    ->form([
                        FileUpload::make('team_banner')
                            ->label('Фото команди')
                            ->image()
                            ->imageEditor()
                            ->directory('team'),
                    ])
                    ->mountUsing(function ($form) {
                        $settings = SiteSetting::first();

                        $form->fill([
                            'team_banner' => $settings?->team_banner,
                        ]);
                    })
                    ->action(function ($data) {
                        $settings = SiteSetting::first();

                        $settings->update([
                            'team_banner' => $data['team_banner'],
                        ]);
                    }),

                Action::make('manage_departments')
                    ->label('Редагувати відділи')

                    ->mountUsing(function ($form) {
                        $form->fill([
                            'departments' => Department::all()->map(fn ($d) => [
                                'id' => $d->id,
                                'name' => $d->name,
                            ])->toArray()
                        ]);
                    })

                    ->form([
                        Repeater::make('departments')
                            ->schema([

                                TextInput::make('id')->hidden(),

                                TextInput::make('name')
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        $clean = preg_replace('/^відділ\s+/iu', '', $state);
                                        $set('name', $clean);
                                    }),

                            ])
                    ])

                    ->action(function (array $data) {

                        $ids = [];

                        foreach ($data['departments'] as $item) {

                            if (!empty($item['id'])) {

                                Department::where('id', $item['id'])->update([
                                    'name' => $item['name'],
                                ]);

                                $ids[] = $item['id'];

                            } else {

                                $new = Department::create([
                                    'name' => $item['name'],
                                ]);

                                $ids[] = $new->id;
                            }
                        }

                        Department::whereNotIn('id', $ids)->delete();
                    }),

                Action::make('manage_positions')
                    ->label('Редагувати посади')
                    ->form([
                        Repeater::make('positions')
                            ->schema([
                                TextInput::make('name')->required(),
                            ])
                            ->default(fn () => Position::all()->toArray()),
                    ])
                    ->action(function ($data) {

                        Position::query()->delete();

                        foreach ($data['positions'] as $item) {
                            Position::create($item);
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