<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\DepartmentResource;
use App\Models\Department;
use App\Services\MediaArchiveManager;
use App\Support\MediaStorage;
use App\Support\TeamDepartmentContent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class TeamDepartmentController extends Controller
{
    public function store(Request $request): DepartmentResource
    {
        return $this->save($request, new Department);
    }

    public function update(Request $request, Department $department): DepartmentResource
    {
        return $this->save($request, $department);
    }

    public function destroy(Request $request, Department $department): JsonResponse
    {
        $this->authorizeManager($request);
        $request->validate(['revision' => ['required', 'string', 'size:64']]);
        $media = DB::transaction(function () use ($request, $department): array {
            $department = Department::query()->lockForUpdate()->findOrFail($department->id);
            $this->checkRevision($request, $department);
            if ($department->employees()->exists()) {
                throw ValidationException::withMessages([
                    'department' => 'Перед видаленням відділу перенесіть його працівників до іншого відділу.',
                ]);
            }
            $media = $this->media($department);
            Department::withoutEvents(fn () => $department->delete());
            system_log('delete_department', 'Видалено відділ з мобільного застосунку: '.$department->name);

            return $media;
        });
        app(MediaArchiveManager::class)->retire($media, 'deleted', $department);

        return response()->json(['message' => 'Відділ видалено.']);
    }

    private function save(Request $request, Department $department): DepartmentResource
    {
        $this->authorizeManager($request);
        if (is_string($request->input('name'))) {
            $request->merge(['name' => trim(preg_replace('/^відділ(?:\s+|$)/iu', '', trim($request->input('name'))))]);
        }
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'max:10000'],
            'slider_enabled' => ['sometimes', 'boolean'],
            'photo_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'remove_photo' => ['nullable', 'boolean'],
            'slider_files' => ['sometimes', 'array', 'max:20'],
            'slider_files.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'gallery_order' => ['sometimes', 'string', 'json'],
            'revision' => [$department->exists ? 'required' : 'sometimes', 'string', 'size:64'],
        ]);
        if ($request->hasFile('photo_file') && $request->boolean('remove_photo')) {
            throw ValidationException::withMessages(['photo_file' => 'Виберіть нове фото або видалення поточного.']);
        }

        $uploaded = [];
        try {
            [$saved, $removed] = DB::transaction(function () use ($request, $department, $data, &$uploaded): array {
                $created = ! $department->exists;
                if (! $created) {
                    $department = Department::query()->lockForUpdate()->findOrFail($department->id);
                    $this->checkRevision($request, $department);
                }
                $previous = $this->media($department);
                $existing = [];
                foreach ($department->slider_photos ?? [] as $path) {
                    $existing['existing:'.TeamDepartmentContent::photoId($path)] = $path;
                }
                $files = array_values($request->file('slider_files', []));
                $order = array_key_exists('gallery_order', $data)
                    ? json_decode($data['gallery_order'], true)
                    : array_keys($existing);
                $valid = is_array($order) && array_is_list($order) && count($order) <= 20;
                $seen = [];
                $uploadIndexes = [];
                foreach ($valid ? $order : [] as $token) {
                    if (! is_string($token) || isset($seen[$token])) {
                        $valid = false;
                        break;
                    }
                    $seen[$token] = true;
                    if (isset($existing[$token])) {
                        continue;
                    }
                    if (! preg_match('/^upload:(0|[1-9]\d*)$/D', $token, $matches) || ! isset($files[(int) $matches[1]])) {
                        $valid = false;
                        break;
                    }
                    $uploadIndexes[] = (int) $matches[1];
                }
                sort($uploadIndexes);
                if (! $valid || $uploadIndexes !== array_keys($files)) {
                    throw ValidationException::withMessages([
                        'gallery_order' => 'Перевірте порядок фото: до 20 зображень, без повторів і сторонніх файлів.',
                    ]);
                }

                $attributes = Arr::only($data, ['name', 'description', 'slider_enabled']);
                if ($request->boolean('remove_photo')) {
                    $attributes['photo'] = null;
                }
                if ($request->hasFile('photo_file')) {
                    $attributes['photo'] = $uploaded[] = $request->file('photo_file')->store('team/departments', 'public');
                }
                $newPhotos = [];
                foreach ($files as $file) {
                    $newPhotos[] = $uploaded[] = $file->store('team/departments', 'public');
                }
                $attributes['slider_photos'] = array_map(
                    fn (string $token): string => $existing[$token] ?? $newPhotos[(int) substr($token, 7)], $order,
                );
                $department->fill($attributes);
                // Старі файли прибираємо лише після успішної транзакції.
                Department::withoutEvents(fn () => $department->save());
                system_log($created ? 'create_department' : 'update_department',
                    ($created ? 'Додано' : 'Оновлено').' відділ з мобільного застосунку: '.$department->name);

                return [$department, array_values(array_diff($previous, $this->media($department)))];
            });
        } catch (Throwable $exception) {
            MediaStorage::delete($uploaded);
            throw $exception;
        }
        $saved->refresh()->optimizeMediaImages(true);
        app(MediaArchiveManager::class)->retire($removed, 'replaced', $saved);

        return new DepartmentResource($saved->refresh());
    }

    private function checkRevision(Request $request, Department $department): void
    {
        abort_unless(hash_equals(TeamDepartmentContent::revision($department), (string) $request->input('revision')), 409,
            'Відділ уже змінено в іншому вікні. Оновіть його перед повторним збереженням.');
    }

    private function media(Department $department): array
    {
        return MediaStorage::paths([$department->photo, $department->slider_photos]);
    }

    private function authorizeManager(Request $request): void
    {
        abort_unless(
            $request->user()->isAdmin() || $request->user()->isEditor(),
            403,
            'Керування відділами доступне адміністраторам і редакторам.',
        );
    }
}
