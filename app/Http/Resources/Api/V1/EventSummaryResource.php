<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class EventSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $summary = $this->summary;

        return [
            'event' => [
                'id' => $this->id,
                'title' => $this->title,
                'event_date' => $this->event_date?->toIso8601String(),
                'status' => $this->status->value,
                'image_url' => $this->image ? route('events.image', $this->resource) : null,
            ],
            'summary' => $summary ? [
                'id' => $summary->id,
                'text' => $summary->summary,
                'status' => $summary->status,
                'published_at' => $summary->published_at?->toIso8601String(),
                'author' => $summary->relationLoaded('author') && $summary->author ? [
                    'id' => $summary->author->id,
                    'name' => $summary->author->name,
                ] : null,
                'smm' => [
                    'title' => $summary->smm_title,
                    'description' => $summary->smm_description,
                    'image_url' => $this->mediaUrl($request, $summary->smm_image),
                ],
                'images' => $summary->relationLoaded('images')
                    ? $summary->images->map(fn ($image) => [
                        'id' => $image->id,
                        'image_url' => $this->mediaUrl($request, $image->image),
                        'alt_text' => $image->alt_text,
                        'sort_order' => (int) $image->sort_order,
                    ])->values()
                    : [],
                'updated_at' => $summary->updated_at?->toIso8601String(),
            ] : null,
        ];
    }

    private function mediaUrl(Request $request, ?string $path): ?string
    {
        return $path ? $request->getSchemeAndHttpHost().Storage::url($path) : null;
    }
}
