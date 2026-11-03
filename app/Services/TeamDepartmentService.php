<?php

namespace App\Services;

use App\Models\Department;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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
            'departments.*.photo' => ['nullable', 'string', 'max:2048', 'starts_with:team/', 'not_regex:/\.\./'],
            'departments.*.slider_enabled' => ['sometimes', 'boolean'],
            'departments.*.slider_photos' => ['sometimes', 'nullable', 'array', 'max:20'],
            'departments.*.slider_photos.*' => ['string', 'max:2048', 'starts_with:team/', 'not_regex:/\.\./'],
        ])->validate();

        $ids = collect($data['departments'])->pluck('id')->filter()->all();
        $removed = Department::query()->whereNotIn('id', $ids)->withCount('employees')->get();

        if ($removed->contains(fn (Department $department): bool => $department->employees_count > 0)) {
            throw ValidationException::withMessages([
                'departments' => 'Перед видаленням відділу перенесіть його працівників до іншого відділу.',
            ]);
        }

        DB::transaction(function () use ($data, $removed): void {
            foreach ($data['departments'] as $item) {
                $department = filled($item['id'] ?? null)
                    ? Department::query()->findOrFail($item['id'])
                    : new Department;
                $attributes = Arr::except($item, ['id']);
                $department->fill($attributes)->save();
            }

            foreach ($removed as $department) {
                $department->delete();
            }
        });

        system_log('update_departments', 'Оновлено відділи, описи та фотослайдери команди.');
    }
}
