<?php

namespace App\Filament\Resources\Employees\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
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
                    ->required(),

                Select::make('department_id')
                    ->label('Відділ')
                    ->relationship('department', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                FileUpload::make('photo')
                    ->label('Фото')
                    ->image()
                    ->disk('public')
                    ->visibility('public')
                    ->directory('employees')

                    // 🔥 ПРОПОРЦІЯ ЯК НА САЙТІ
                    ->imageCropAspectRatio('3:4')

                    // 🔥 РОЗМІР (щоб не ламав верстку)
                    ->imageResizeTargetWidth('600')
                    ->imageResizeTargetHeight('800')

                    // 🔥 DRAG / ZOOM (вже є в Filament crop)
                    ->panelAspectRatio('3:4')
                    ->panelLayout('integrated')
                    ->imageEditor()

                    // 🔥 ПРЕВʼЮ
                    ->imagePreviewHeight('250')

                    // 🔥 FALLBACK
                    ->default(null)
                    ->avatar(fn ($state) =>
                        $state
                            ? asset('storage/' . $state)
                            : asset('images/default-avatar.png')
                    ),

                TextInput::make('sort')
                    ->numeric()
                    ->default(0)
                    ->hidden(),

            ]);
    }
}
