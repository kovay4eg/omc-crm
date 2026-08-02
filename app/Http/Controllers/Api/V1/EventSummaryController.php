<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\EventSummaryResource;
use App\Models\Event;
use App\Models\EventSummary;
use App\Models\EventSummaryHistory;
use App\Models\EventSummaryImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EventSummaryController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Event::query()->with(['summary.author', 'summary.images'])->latest('event_date');
        if ($search = trim((string) $request->query('search'))) {
            $query->where('title', 'like', "%{$search}%");
        }
        if ($status = $request->query('summary_status')) {
            $query->whereHas('summary', fn ($builder) => $builder->where('status', $status));
        }

        return EventSummaryResource::collection($query->paginate(min(max((int) $request->query('per_page', 30), 1), 100)));
    }

    public function update(Request $request, Event $event): EventSummaryResource
    {
        $data = $request->validate([
            'summary' => ['required', 'string'],
            'status' => ['required', Rule::in([EventSummary::STATUS_DRAFT, EventSummary::STATUS_PUBLISHED])],
            'smm_title' => ['nullable', 'string', 'max:255'],
            'smm_description' => ['nullable', 'string', 'max:500'],
            'smm_image_file' => ['nullable', 'image', 'max:10240'],
            'remove_smm_image' => ['nullable', 'boolean'],
            'images' => ['nullable', 'array', 'max:20'],
            'images.*' => ['image', 'max:10240'],
        ]);

        DB::transaction(function () use ($request, $event, $data): void {
            $summary = $event->summary()->firstOrNew();
            $created = ! $summary->exists;
            $old = $summary->exists ? $summary->only(['summary', 'status', 'smm_title', 'smm_description']) : [];
            $attributes = Arr::only($data, ['summary', 'status', 'smm_title', 'smm_description']);
            $attributes['user_id'] = $request->user()->id;
            $attributes['published_at'] = $data['status'] === EventSummary::STATUS_PUBLISHED
                ? ($summary->published_at ?? now())
                : null;
            if ($request->boolean('remove_smm_image')) {
                $attributes['smm_image'] = null;
            }
            if ($request->hasFile('smm_image_file')) {
                $attributes['smm_image'] = $request->file('smm_image_file')->store('event-summaries/smm', 'public');
            }
            $summary->fill($attributes)->save();

            $nextSort = (int) $summary->images()->max('sort_order') + 1;
            foreach ($request->file('images', []) as $image) {
                $summary->images()->create([
                    'image' => $image->store('event-summaries/gallery', 'public'),
                    'sort_order' => $nextSort++,
                ]);
            }

            EventSummaryHistory::query()->create([
                'event_summary_id' => $summary->id,
                'user_id' => $request->user()->id,
                'action' => $created ? 'created' : 'updated',
                'description' => ($created ? 'Створено' : 'Оновлено').' підсумок через мобільний застосунок.',
                'changes' => $created ? null : ['before' => $old, 'after' => $summary->only(array_keys($old))],
            ]);
            system_log($created ? 'create_event_summary' : 'update_event_summary',
                ($created ? 'Створено' : 'Оновлено').' підсумок заходу з мобільного застосунку: '.$event->title);
        });

        return new EventSummaryResource($event->refresh()->load(['summary.author', 'summary.images']));
    }

    public function destroyImage(Request $request, EventSummaryImage $image): JsonResponse
    {
        $eventTitle = $image->eventSummary()->with('event')->first()?->event?->title;
        $image->delete();
        system_log('delete_event_summary_image', 'Видалено фото підсумку з мобільного застосунку: '.($eventTitle ?: 'захід'));

        return response()->json(['message' => 'Фото видалено.']);
    }

    public function reorderImages(Request $request, EventSummary $summary): JsonResponse
    {
        $data = $request->validate(['image_ids' => ['required', 'array'], 'image_ids.*' => ['integer', 'distinct']]);
        $valid = $summary->images()->whereKey($data['image_ids'])->pluck('id')->all();
        abort_unless(count($valid) === count($data['image_ids']), 422, 'Список містить сторонні фотографії.');
        foreach ($data['image_ids'] as $sort => $id) {
            $summary->images()->whereKey($id)->update(['sort_order' => $sort]);
        }
        system_log('reorder_event_summary_images', 'Змінено порядок фото підсумку з мобільного застосунку.');

        return response()->json(['message' => 'Порядок фотографій збережено.']);
    }
}
