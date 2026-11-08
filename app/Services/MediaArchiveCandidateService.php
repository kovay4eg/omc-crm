<?php

namespace App\Services;

use App\Models\AppAnnouncement;
use App\Models\Event;
use App\Models\EventSummary;
use App\Models\EventSummaryImage;
use App\Models\MediaArchive;
use App\Support\MediaStorage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Throwable;

class MediaArchiveCandidateService
{
    public function __construct(private readonly MediaReferenceCatalog $references) {}

    /**
     * @return array<int, array{path: string, reason: string, source_type: string|null, source_id: int|null, priority: int, size: int}>
     */
    public function candidates(?int $limit = null): array
    {
        $limit ??= max(1, (int) config('media_archive.batch_size', 200));
        $candidates = [];

        $this->addPending($candidates);
        $this->addOrphans($candidates);
        $this->addExpiredAnnouncements($candidates);
        $this->addOldEventsAndSummaries($candidates);

        usort($candidates, static fn (array $left, array $right): int => [$left['priority'], -$left['size'], $left['path']]
            <=> [$right['priority'], -$right['size'], $right['path']]);

        return array_slice($candidates, 0, $limit);
    }

    /**
     * @param  array<string, array{path: string, reason: string, source_type: string|null, source_id: int|null, priority: int, size: int}>  $candidates
     */
    private function addPending(array &$candidates): void
    {
        if (! Schema::hasTable('media_archives')) {
            return;
        }

        $staleBefore = now()->subMinutes(max(1, (int) config('media_archive.stale_upload_minutes', 30)));

        MediaArchive::query()
            ->where(function ($query) use ($staleBefore): void {
                $query->where(function ($query): void {
                    $query->whereIn('status', [
                        MediaArchive::STATUS_PENDING,
                        MediaArchive::STATUS_FAILED,
                        MediaArchive::STATUS_VERIFIED,
                    ])->where(fn ($query) => $query->whereNull('eligible_at')->orWhere('eligible_at', '<=', now()));
                })->orWhere(function ($query) use ($staleBefore): void {
                    $query->whereIn('status', [
                        MediaArchive::STATUS_UPLOADING,
                        MediaArchive::STATUS_UPLOADED,
                    ])->where('updated_at', '<=', $staleBefore);
                });
            })
            ->orderBy('eligible_at')
            ->each(function (MediaArchive $archive) use (&$candidates): void {
                $this->add($candidates, [
                    'path' => $archive->original_path,
                    'reason' => $archive->reason ?: 'replaced',
                    'source_type' => $archive->source_type,
                    'source_id' => $archive->source_id,
                    'priority' => 10,
                ]);
            });
    }

    /**
     * @param  array<string, array{path: string, reason: string, source_type: string|null, source_id: int|null, priority: int, size: int}>  $candidates
     */
    private function addOrphans(array &$candidates): void
    {
        $referenced = array_fill_keys($this->references->paths(), true);

        if (Schema::hasTable('media_archives')) {
            foreach (MediaArchive::query()
                ->where('eligible_at', '>', now())
                ->pluck('original_path') as $protectedPath) {
                // A replaced file may no longer be referenced by content, but
                // its explicit grace period must still win over orphan rules.
                $referenced[(string) $protectedPath] = true;
            }
        }

        $olderThan = now()->subDays(max(0, (int) config('media_archive.retention.orphan_grace_days', 7)))->getTimestamp();

        foreach ($this->references->storageRoots() as $root) {
            foreach (MediaStorage::managedDirectories() as $directory) {
                $sourceDirectory = $root['prefix'].$directory;

                foreach (Storage::disk($root['disk'])->allFiles($sourceDirectory) as $file) {
                    $relativePath = str_starts_with($file, $root['prefix'])
                        ? substr($file, strlen($root['prefix']))
                        : $file;

                    if (isset($referenced[$relativePath])) {
                        continue;
                    }

                    try {
                        if (Storage::disk($root['disk'])->lastModified($file) > $olderThan) {
                            continue;
                        }
                    } catch (Throwable) {
                        // A missing/unstable file will be skipped by add().
                    }

                    $this->add($candidates, [
                        'path' => $relativePath,
                        'reason' => 'orphan',
                        'source_type' => null,
                        'source_id' => null,
                        'priority' => 20,
                    ]);
                }
            }
        }
    }

    /**
     * @param  array<string, array{path: string, reason: string, source_type: string|null, source_id: int|null, priority: int, size: int}>  $candidates
     */
    private function addExpiredAnnouncements(array &$candidates): void
    {
        if (! Schema::hasTable((new AppAnnouncement)->getTable())) {
            return;
        }

        $cutoff = now()->subDays(max(0, (int) config('media_archive.retention.announcement_days', 90)));

        AppAnnouncement::query()
            ->whereNotNull('image_path')
            ->where('expires_at', '<=', $cutoff)
            ->each(function (AppAnnouncement $announcement) use (&$candidates): void {
                foreach (MediaStorage::paths($announcement->image_path) as $path) {
                    $this->add($candidates, [
                        'path' => $path,
                        'reason' => 'old_announcement',
                        'source_type' => $announcement->getMorphClass(),
                        'source_id' => (int) $announcement->getKey(),
                        'priority' => 30,
                    ]);
                }
            });
    }

    /**
     * @param  array<string, array{path: string, reason: string, source_type: string|null, source_id: int|null, priority: int, size: int}>  $candidates
     */
    private function addOldEventsAndSummaries(array &$candidates): void
    {
        if (! Schema::hasTable((new Event)->getTable())) {
            return;
        }

        $cutoff = now()->subDays(max(0, (int) config('media_archive.retention.event_days', 180)));

        Event::query()
            ->where('event_date', '<=', $cutoff)
            ->each(function (Event $event) use (&$candidates): void {
                $this->addModelPaths($candidates, $event, [$event->image, $event->smm_image], 'old_event', 40);
            });

        if (! Schema::hasTable((new EventSummary)->getTable())) {
            return;
        }

        EventSummary::query()
            ->with('images')
            ->where(function ($query) use ($cutoff): void {
                $query->where('published_at', '<=', $cutoff)
                    ->orWhere(fn ($query) => $query->whereNull('published_at')->where('created_at', '<=', $cutoff));
            })
            ->each(function (EventSummary $summary) use (&$candidates): void {
                $this->addModelPaths($candidates, $summary, [$summary->smm_image], 'old_summary', 50);

                if (! Schema::hasTable((new EventSummaryImage)->getTable())) {
                    return;
                }

                foreach ($summary->images as $image) {
                    $this->addModelPaths(
                        $candidates,
                        $image,
                        [$image->image],
                        'old_summary',
                        50,
                    );
                }
            });
    }

    /**
     * @param  array<string, array{path: string, reason: string, source_type: string|null, source_id: int|null, priority: int, size: int}>  $candidates
     * @param  array<int, mixed>  $values
     */
    private function addModelPaths(array &$candidates, Model $model, array $values, string $reason, int $priority): void
    {
        foreach (MediaStorage::paths($values) as $path) {
            $this->add($candidates, [
                'path' => $path,
                'reason' => $reason,
                'source_type' => $model->getMorphClass(),
                'source_id' => (int) $model->getKey(),
                'priority' => $priority,
            ]);
        }
    }

    /**
     * @param  array<string, array{path: string, reason: string, source_type: string|null, source_id: int|null, priority: int, size: int}>  $candidates
     * @param  array{path: string, reason: string, source_type: string|null, source_id: int|null, priority: int}  $candidate
     */
    private function add(array &$candidates, array $candidate): void
    {
        $location = MediaStorage::find($candidate['path']);

        if ($location === null || ! is_file($location['absolute_path'])) {
            return;
        }

        $candidate['size'] = (int) filesize($location['absolute_path']);
        $existing = $candidates[$candidate['path']] ?? null;

        if ($existing === null || $candidate['priority'] < $existing['priority']) {
            $candidates[$candidate['path']] = $candidate;
        }
    }
}
