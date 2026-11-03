<?php

namespace App\Http\Resources\Api\V1;

use App\Support\TeamDepartmentContent;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DepartmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'photo_url' => $this->photo ? route('departments.photo', [
                'department' => $this->id, 'filename' => basename($this->photo),
                'v' => substr(sha1($this->photo), 0, 12),
            ]) : null,
            'slider_enabled' => (bool) $this->slider_enabled,
            'slider_photos' => collect(array_values($this->slider_photos ?? []))
                ->map(fn (string $path, int $index): array => [
                    'id' => TeamDepartmentContent::photoId($path),
                    'name' => basename($path),
                    'url' => route('departments.slide', [
                        'department' => $this->id, 'index' => $index,
                        'filename' => basename($path), 'v' => substr(sha1($path), 0, 12),
                    ]),
                ])->all(),
            'revision' => TeamDepartmentContent::revision($this->resource),
        ];
    }
}
