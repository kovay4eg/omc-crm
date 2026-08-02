<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ContentDocumentResource;
use App\Models\CalendarPlan;
use App\Models\Report;
use App\Models\Statute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ContentDocumentController extends Controller
{
    public function index(Request $request, string $type): AnonymousResourceCollection
    {
        $model = $this->model($type);
        $query = $model::query();
        if ($search = trim((string) $request->query('search'))) {
            $query->where('title', 'like', "%{$search}%");
        }
        if ($type !== 'statutes') {
            $query->orderByDesc('year');
        }

        return ContentDocumentResource::collection(
            $query->latest('id')->paginate(min(max((int) $request->query('per_page', 50), 1), 100)),
        );
    }

    public function store(Request $request, string $type): ContentDocumentResource
    {
        $model = $this->model($type);
        $data = $request->validate($this->rules($type, true));
        $document = new $model;
        $document->title = $data['title'];
        if ($type !== 'statutes') {
            $document->year = $data['year'];
        }
        $document->file = $request->file('file')->store($this->directory($type), 'public');
        $document->save();

        system_log('create_'.$this->logName($type), 'Створено документ з мобільного застосунку: '.$document->title);

        return new ContentDocumentResource($document);
    }

    public function update(Request $request, string $type, int $document): ContentDocumentResource
    {
        $record = $this->find($type, $document);
        $data = $request->validate($this->rules($type, false));
        $record->title = $data['title'];
        if ($type !== 'statutes') {
            $record->year = $data['year'];
        }
        if ($request->hasFile('file')) {
            $record->file = $request->file('file')->store($this->directory($type), 'public');
        }
        $record->save();

        system_log('update_'.$this->logName($type), 'Оновлено документ з мобільного застосунку: '.$record->title);

        return new ContentDocumentResource($record->refresh());
    }

    public function destroy(string $type, int $document): JsonResponse
    {
        $record = $this->find($type, $document);
        $title = $record->title;
        $record->delete();
        system_log('delete_'.$this->logName($type), 'Видалено документ з мобільного застосунку: '.$title);

        return response()->json(['message' => 'Документ видалено.']);
    }

    private function rules(string $type, bool $fileRequired): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'year' => [$type === 'statutes' ? 'nullable' : 'required', 'integer', 'min:2000', 'max:2100'],
            'file' => [$fileRequired ? 'required' : 'nullable', 'file', 'max:20480', 'mimes:pdf,doc,docx,xls,xlsx'],
        ];
    }

    /** @return class-string<Model> */
    private function model(string $type): string
    {
        return match ($type) {
            'reports' => Report::class,
            'calendar-plans' => CalendarPlan::class,
            'statutes' => Statute::class,
            default => abort(404, 'Невідомий тип документів.'),
        };
    }

    private function find(string $type, int $id): Model
    {
        $model = $this->model($type);

        return $model::query()->findOrFail($id);
    }

    private function directory(string $type): string
    {
        return match ($type) {
            'calendar-plans' => 'calendar-plans',
            default => $type,
        };
    }

    private function logName(string $type): string
    {
        return str_replace('-', '_', rtrim($type, 's'));
    }
}
