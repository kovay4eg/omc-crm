<?php

namespace App\Filament\Resources\EventSummaries\Pages;

use App\Filament\Resources\EventSummaries\EventSummaryResource;
use App\Models\EventSummary;
use App\Models\EventSummaryHistory;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditEventSummary extends EditRecord
{
    protected static string $resource = EventSummaryResource::class;

    protected bool $summaryExistedBeforeSave = false;

    protected array $summaryBeforeSave = [];

    protected array $imageIdsBeforeSave = [];

    protected array $newImages = [];

    public function getTitle(): string
    {
        return $this->record->summary()->exists()
            ? 'Редагувати підсумок заходу'
            : 'Створити підсумок заходу';
    }

    protected function beforeSave(): void
    {
        $summary = $this->record
            ->summary()
            ->with('images')
            ->first();

        $this->summaryExistedBeforeSave = $summary !== null;

        $this->summaryBeforeSave = $summary?->only([
            'summary',
            'status',
        ]) ?? [];

        $this->imageIdsBeforeSave = $summary?->images->modelKeys() ?? [];
    }

    protected function afterSave(): void
    {
        $summary = $this->record
            ->summary()
            ->with('images')
            ->first();

        if (! $summary) {
            return;
        }

        $nextSortOrder = (int) $summary->images()->max('sort_order');

        foreach (array_unique($this->newImages) as $imagePath) {
            if (! is_string($imagePath) || $imagePath === '') {
                continue;
            }

            if ($summary->images()->where('image', $imagePath)->exists()) {
                continue;
            }

            $summary->images()->create([
                'image' => $imagePath,
                'sort_order' => ++$nextSortOrder,
            ]);
        }

        if (
            $summary->status === EventSummary::STATUS_PUBLISHED
            && $summary->published_at === null
        ) {
            $summary->update([
                'published_at' => now(),
            ]);
        }

        if (
            $summary->status === EventSummary::STATUS_DRAFT
            && $summary->published_at !== null
        ) {
            $summary->update([
                'published_at' => null,
            ]);
        }

        $summary->refresh()->load('images');

        $changes = [];

        if (
            $this->summaryExistedBeforeSave
            && ($this->summaryBeforeSave['summary'] ?? null) !== $summary->summary
        ) {
            $changes['summary'] = [
                'old' => $this->summaryBeforeSave['summary'] ?? null,
                'new' => $summary->summary,
            ];
        }

        if (
            $this->summaryExistedBeforeSave
            && ($this->summaryBeforeSave['status'] ?? null) !== $summary->status
        ) {
            $changes['status'] = [
                'old' => $this->summaryBeforeSave['status'] ?? null,
                'new' => $summary->status,
            ];
        }

        $imageIdsAfterSave = $summary->images->modelKeys();

        $addedImages = array_values(array_diff(
            $imageIdsAfterSave,
            $this->imageIdsBeforeSave,
        ));

        $removedImages = array_values(array_diff(
            $this->imageIdsBeforeSave,
            $imageIdsAfterSave,
        ));

        if ($addedImages || $removedImages) {
            $changes['images'] = [
                'added' => count($addedImages),
                'removed' => count($removedImages),
            ];
        }

        if (! $this->summaryExistedBeforeSave) {
            $action = 'created';

            $description = $summary->status === EventSummary::STATUS_PUBLISHED
                ? 'Створено та опубліковано підсумок заходу.'
                : 'Створено чернетку підсумку заходу.';
        } elseif (
            ($this->summaryBeforeSave['status'] ?? null) !== EventSummary::STATUS_PUBLISHED
            && $summary->status === EventSummary::STATUS_PUBLISHED
        ) {
            $action = 'published';
            $description = 'Опубліковано підсумок заходу.';
        } elseif (
            ($this->summaryBeforeSave['status'] ?? null) === EventSummary::STATUS_PUBLISHED
            && $summary->status === EventSummary::STATUS_DRAFT
        ) {
            $action = 'unpublished';
            $description = 'Підсумок заходу знято з публікації.';
        } elseif ($changes) {
            $action = 'updated';
            $description = 'Оновлено підсумок заходу.';
        } else {
            return;
        }

        EventSummaryHistory::create([
            'event_summary_id' => $summary->id,
            'user_id' => auth()->id(),
            'action' => $action,
            'description' => $description,
            'changes' => $changes ?: null,
        ]);

        system_log(
            'update_event_summary',
            $description . ' Захід: ' . $this->record->title
        );
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->newImages = array_values(array_filter(
            $data['new_images'] ?? [],
            fn ($imagePath) => is_string($imagePath) && $imagePath !== '',
        ));

        unset($data['new_images']);

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')
                ->label('Попередній перегляд')
                ->icon(Heroicon::Eye)
                ->color('gray')
                ->url(fn (): string => route('event-summaries.preview', [
                    'event' => $this->record,
                ]))
                ->openUrlInNewTab()
                ->visible(fn (): bool => $this->record->summary()->exists()),
        ];
    }
}
