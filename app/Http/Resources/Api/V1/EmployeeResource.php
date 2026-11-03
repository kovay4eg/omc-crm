<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'middle_name' => $this->middle_name,
            'description' => $this->description,
            'full_name' => trim(implode(' ', array_filter([
                $this->last_name,
                $this->first_name,
                $this->middle_name,
            ]))),
            'photo_url' => $this->photo
                ? route('employees.photo', ['employee' => $this->id, 'filename' => basename($this->photo), 'v' => substr(sha1($this->photo), 0, 12)])
                : null,
            'sort' => (int) $this->sort,
            'position' => $this->whenLoaded('position', fn () => $this->position ? [
                'id' => $this->position->id,
                'name' => $this->position->name,
            ] : null),
            'department' => $this->whenLoaded('department', fn () => $this->department ? [
                'id' => $this->department->id,
                'name' => $this->department->name,
            ] : null),
        ];
    }
}
