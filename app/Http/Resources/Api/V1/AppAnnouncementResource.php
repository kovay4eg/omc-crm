<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class AppAnnouncementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'body' => $this->body,
            'image_url' => $this->image_path
                ? $request->getSchemeAndHttpHost().Storage::url($this->image_path)
                : null,
            'link_url' => $this->link_url,
            'link_label' => $this->link_label,
            'starts_at' => $this->starts_at?->toIso8601String(),
            'expires_at' => $this->expires_at?->toIso8601String(),
            'is_active' => $this->is_active,
            'push_requested_at' => $this->push_requested_at?->toIso8601String(),
            'push_sent_at' => $this->push_sent_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
