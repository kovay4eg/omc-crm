<?php

namespace App\Filament\Resources\Employees\Schemas;

use App\Support\MediaStorage;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
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

                Textarea::make('description')
                    ->label('Опис працівника')
                    ->rows(5)
                    ->maxLength(5000)
                    ->columnSpanFull(),

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
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->maxSize(8192)
                    ->helperText('JPEG, PNG або WebP, до 8 МБ.')
                    ->disk('public')
                    ->visibility('public')
                    ->directory('employees')
                    ->fetchFileInformation(false)
                    ->getUploadedFileUsing(static function (FileUpload $component, string $file, string|array|null $storedFileNames): ?array {
                        $record = $component->getRecord();

                        return MediaStorage::uploadedFileDetails(
                            $file,
                            $storedFileNames,
                            $record ? route('employees.photo', ['employee' => $record, 'filename' => basename($file), 'v' => substr(sha1($file), 0, 12)]) : null,
                        );
                    })

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
                    ->imagePreviewHeight('250'),

                TextInput::make('sort')
                    ->numeric()
                    ->default(0)
                    ->hidden(),

            ]);
    }
}
