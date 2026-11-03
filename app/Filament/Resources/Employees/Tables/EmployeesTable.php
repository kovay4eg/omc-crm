<?php

namespace App\Filament\Resources\Employees\Tables;

use App\Filament\Resources\Employees\Actions\ManageDepartments;
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

                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])

                            ->maxSize(10240)

                            ->disk('public')

                            ->visibility('public')

                            ->directory('team')

                            ->imageEditor()

                            ->imageEditorMode(2)

                            ->imageEditorAspectRatios([
                                '21:9',
                            ])

                            ->imageCropAspectRatio('21:9')

                            ->panelAspectRatio('21:9')

                            ->panelLayout('integrated')

                            ->removeUploadedFileButtonPosition('right')

                            ->uploadProgressIndicatorPosition('left')

                            ->openable()

                            ->downloadable()

                            ->previewable(true),

                    ])

                    ->mountUsing(function ($form) {

                        $settings = SiteSetting::first();

                        if (! $settings) {

                            $settings = SiteSetting::create([
                                'team_banner' => null,
                            ]);

                        }

                        $form->fill([
                            'team_banner' => $settings->team_banner,
                        ]);
                    })

                    ->action(function ($data) {

                        $settings = SiteSetting::first();

                        if (! $settings) {

                            $settings = SiteSetting::create([
                                'team_banner' => null,
                            ]);

                        }

                        $settings->update([
                            'team_banner' => $data['team_banner'],
                        ]);
                    }),

                ManageDepartments::make(),

                Action::make('manage_positions')
                    ->label('Редагувати посади')

                    ->mountUsing(function ($form) {

                        $form->fill([
                            'positions' => Position::all()->map(fn ($p) => [
                                'id' => $p->id,
                                'name' => $p->name,
                            ])->toArray(),
                        ]);

                    })

                    ->form([

                        Repeater::make('positions')
                            ->schema([

                                TextInput::make('id')
                                    ->hidden(),

                                TextInput::make('name')
                                    ->required(),

                            ]),

                    ])

                    ->action(function ($data) {

                        $ids = [];

                        foreach ($data['positions'] as $item) {

                            if (! empty($item['id'])) {

                                Position::where('id', $item['id'])->update([
                                    'name' => $item['name'],
                                ]);

                                $ids[] = $item['id'];

                            } else {

                                $exists = Position::where('name', $item['name'])->first();

                                if ($exists) {

                                    $ids[] = $exists->id;

                                } else {

                                    $new = Position::create([
                                        'name' => $item['name'],
                                    ]);

                                    $ids[] = $new->id;
                                }
                            }
                        }

                        Position::whereNotIn('id', $ids)->delete();

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
