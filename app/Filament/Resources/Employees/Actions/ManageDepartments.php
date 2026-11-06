<?php

namespace App\Filament\Resources\Employees\Actions;

use App\Models\Department;
use App\Services\TeamDepartmentService;
use App\Support\MediaStorage;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class ManageDepartments
{
    public static function make(): Action
    {
        return Action::make('manage_departments')
            ->label('Редагувати відділи')
            ->visible(fn (): bool => in_array(auth()->user()?->getActiveRole(), ['admin', 'editor'], true))
            ->modalWidth('5xl')
            ->modalSubmitActionLabel('Зберегти')
            ->mountUsing(function ($form): void {
                $form->fill(['departments' => Department::query()->orderBy('id')->get()->map(fn (Department $department): array => [
                    'id' => $department->id,
                    'name' => $department->name,
                    'description' => $department->description,
                    'photo' => $department->photo,
                    'slider_enabled' => $department->slider_enabled,
                    'slider_photos' => $department->slider_photos ?? [],
                ])->all()]);
            })
            ->schema([
                Repeater::make('departments')
                    ->label('Відділи команди')
                    ->itemLabel(fn (array $state): ?string => $state['name'] ?? 'Новий відділ')
                    ->addActionLabel('Додати відділ')
                    ->reorderable(false)
                    ->collapsible()
                    ->schema([
                        Hidden::make('id'),
                        TextInput::make('name')->label('Назва відділу')->required()->maxLength(255)->live(onBlur: true),
                        Textarea::make('description')
                            ->label('Чим займається відділ?')
                            ->rows(5)
                            ->maxLength(10000)
                            ->hidden(fn (Get $get): bool => Department::isAdministrationName($get('name'))),
                        self::upload('photo', 'Загальне фото відділу'),
                        Toggle::make('slider_enabled')
                            ->label('Увімкнути фотослайдер')
                            ->helperText('Вимкнення приховує слайдер на сайті та зберігає завантажені фотографії.')
                            ->default(false)
                            ->live()
                            ->hidden(fn (Get $get): bool => Department::isAdministrationName($get('name'))),
                        self::upload('slider_photos', 'Фотографії слайдера')
                            ->multiple()
                            ->reorderable()
                            ->appendFiles()
                            ->maxFiles(20)
                            ->helperText('Відредагуйте фото кнопкою редактора, перегляньте його або перетягніть для зміни порядку. До 20 фотографій.')
                            ->hidden(fn (Get $get): bool => Department::isAdministrationName($get('name')) || ! $get('slider_enabled'))
                            ->dehydratedWhenHidden(fn (Get $get): bool => ! Department::isAdministrationName($get('name'))),
                    ]),
            ])
            ->action(function (array $data, Schema $schema): void {
                try {
                    app(TeamDepartmentService::class)->save($data['departments'] ?? [], auth()->user());
                } catch (ValidationException $exception) {
                    throw ValidationException::withMessages([
                        $schema->getStatePath().'.departments' => Arr::flatten($exception->errors()),
                    ]);
                }
            });
    }

    private static function upload(string $field, string $label): FileUpload
    {
        return FileUpload::make($field)
            ->label($label)
            ->image()
            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->maxSize(10240)
            ->disk('public')
            ->visibility('public')
            ->directory('team/departments')
            ->imageEditor()
            ->imageEditorAspectRatios([null, '21:9', '16:9', '4:3', '1:1'])
            ->imagePreviewHeight('180')
            ->openable()
            ->downloadable()
            ->previewable()
            ->fetchFileInformation(false)
            ->getUploadedFileUsing(function (FileUpload $component, string $file, string|array|null $storedFileNames, Get $get) use ($field): ?array {
                $department = filled($get('id')) ? Department::query()->find($get('id')) : null;
                $url = null;

                // Редактор Filament бере розширення файлу з останньої частини URL.
                if ($department && $field === 'photo' && $department->photo === $file) {
                    $url = route('departments.photo', ['department' => $department, 'filename' => basename($file), 'v' => substr(sha1($file), 0, 12)]);
                } elseif ($department && $field === 'slider_photos') {
                    $index = array_search($file, array_values($department->slider_photos ?? []), true);
                    $url = $index !== false ? route('departments.slide', ['department' => $department, 'index' => $index, 'filename' => basename($file), 'v' => substr(sha1($file), 0, 12)]) : null;
                }

                return MediaStorage::uploadedFileDetails($file, $storedFileNames, $url);
            });
    }
}
