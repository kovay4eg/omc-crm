<?php

namespace App\Services;

use App\Models\Department;
use App\Models\User;
use App\Support\MediaStorage;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class TeamDepartmentService
{
    public function save(array $departments, User $actor): void
    {
        abort_unless($actor->isAdmin(), 403);

        foreach ($departments as &$departmentData) {
            if (isset($departmentData['name']) && is_string($departmentData['name'])) {
                $departmentData['name'] = trim(preg_replace('/^відділ(?:\s+|$)/iu', '', trim($departmentData['name'])));
            }
        }
        unset($departmentData);

        $data = Validator::make(['departments' => $departments], [
            'departments' => ['present', 'array'],
            'departments.*' => ['array'],
            'departments.*.id' => ['nullable', 'integer', 'distinct', Rule::exists('departments', 'id')],
            'departments.*.name' => ['required', 'string', 'max:255'],
            'departments.*.description' => ['nullable', 'string', 'max:10000'],
            'departments.*.photo' => ['nullable', 'string', 'max:2048', 'starts_with:team/departments/', 'not_regex:/\.\./'],
            'departments.*.slider_enabled' => ['sometimes', 'boolean'],
            'departments.*.slider_photos' => ['sometimes', 'nullable', 'array', 'max:20'],
            'departments.*.slider_photos.*' => ['string', 'max:2048', 'starts_with:team/departments/', 'not_regex:/\.\./'],
        ])->validate();

        $ids = collect($data['departments'])->pluck('id')->filter()->all();
        $removed = Department::query()->whereNotIn('id', $ids)->withCount('employees')->get();

        if ($removed->contains(fn (Department $department): bool => $department->employees_count > 0)) {
            throw ValidationException::withMessages([
                'departments' => 'Перед видаленням відділу перенесіть його працівників до іншого відділу.',
            ]);
        }

        $submittedMedia = MediaStorage::paths(collect($data['departments'])
            ->flatMap(fn (array $item): array => [$item['photo'] ?? null, $item['slider_photos'] ?? []])
            ->all());
        $staleMedia = [];

        try {
            DB::transaction(function () use ($data, $removed, &$staleMedia): void {
                foreach ($data['departments'] as $item) {
                    $department = filled($item['id'] ?? null)
                        ? Department::query()->findOrFail($item['id'])
                        : new Department;
                    $previousMedia = $this->media($department);
                    $attributes = Arr::except($item, ['id']);
                    $department->fill($attributes)->saveQuietly();
                    $staleMedia = [...$staleMedia, ...array_diff($previousMedia, $this->media($department))];
                }

                foreach ($removed as $department) {
                    $staleMedia = [...$staleMedia, ...$this->media($department)];
                    $department->deleteQuietly();
                }
            });
        } catch (Throwable $exception) {
            // Livewire зберігає нові завантаження до виклику сервісу. Якщо БД
            // відкотилася, прибираємо лише ті файли, які не використовує жоден відділ.
            $this->deleteUnreferenced($submittedMedia);

            throw $exception;
        }

        $this->deleteUnreferenced($staleMedia);

        system_log('update_departments', 'Оновлено відділи, описи та фотослайдери команди.');
    }

    /**
     * @return array<int, string>
     */
    private function media(Department $department): array
    {
        return MediaStorage::paths([$department->photo, $department->slider_photos]);
    }

    /**
     * @param  array<int, string>  $paths
     */
    private function deleteUnreferenced(array $paths): void
    {
        if ($paths === []) {
            return;
        }

        $referenced = MediaStorage::paths(Department::query()
            ->get(['photo', 'slider_photos'])
            ->flatMap(fn (Department $department): array => [$department->photo, $department->slider_photos])
            ->all());

        MediaStorage::delete(array_values(array_diff(array_unique($paths), $referenced)));
    }
}
