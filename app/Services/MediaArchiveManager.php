<?php

namespace App\Services;

use App\Contracts\CloudArchiveClient;
use App\Models\MediaArchive;
use App\Support\MediaStorage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class MediaArchiveManager
{
    public function __construct(private readonly CloudArchiveClient $cloud) {}

    /**
     * Marks replaced/deleted user media for safe asynchronous archiving. The
     * local file deliberately stays in place until upload and verification.
     */
    public function retire(mixed $paths, string $reason, ?Model $source = null, int $graceDays = 0): void
    {
        // During a rolling deploy the application code may become active a few
        // seconds before the migration. In that case we keep every local file.
        if (! Schema::hasTable('media_archives')) {
            return;
        }

        foreach (MediaStorage::paths($paths) as $path) {
            $location = MediaStorage::find($path);

            if ($location === null) {
                continue;
            }

            try {
                $archive = MediaArchive::query()->firstOrNew(['original_path' => $path]);

                // A path is normally immutable. If it is reused after a prior
                // archive, reset the remote metadata so new bytes are verified.
                if ($archive->exists && in_array($archive->status, [
                    MediaArchive::STATUS_VERIFIED,
                    MediaArchive::STATUS_ARCHIVED,
                ], true)) {
                    $archive->fill([
                        'remote_path' => null,
                        'remote_file_id' => null,
                        'remote_revision_id' => null,
                        'sha256' => null,
                        'size' => null,
                        'scan_status' => null,
                        'uploaded_at' => null,
                        'verified_at' => null,
                        'archived_at' => null,
                    ]);
                }

                $archive->fill([
                    'source_disk' => $location['disk'],
                    'reason' => $reason,
                    'source_type' => $source?->getMorphClass(),
                    'source_id' => $source?->getKey(),
                    'status' => MediaArchive::STATUS_PENDING,
                    'eligible_at' => now()->addDays(max(0, $graceDays)),
                    'last_error' => null,
                ])->save();
            } catch (Throwable) {
                // Archival bookkeeping must never turn into destructive
                // fallback logic or make a content update fail.
            }
        }
    }

    /**
     * @param  array{path: string, reason: string, source_type: string|null, source_id: int|null, priority?: int, size?: int}  $candidate
     */
    public function archiveCandidate(array $candidate): int
    {
        if (! Schema::hasTable('media_archives')) {
            throw new RuntimeException('Таблиця архіву медіа ще не створена.');
        }

        $location = MediaStorage::find($candidate['path']);

        if ($location === null) {
            return 0;
        }

        $archive = MediaArchive::query()->firstOrNew(['original_path' => $candidate['path']]);
        $archive->fill([
            'source_disk' => $location['disk'],
            'reason' => $candidate['reason'],
            'source_type' => $candidate['source_type'],
            'source_id' => $candidate['source_id'],
            'status' => MediaArchive::STATUS_PENDING,
            'eligible_at' => $archive->eligible_at ?? now(),
        ])->save();

        return $this->archive($archive);
    }

    /**
     * Uploads, downloads and hashes the complete remote object, then deletes
     * only local copies whose bytes still match the verified SHA-256.
     *
     * @return int number of local bytes actually released
     */
    public function archive(MediaArchive $archive): int
    {
        if (! (bool) config('media_archive.enabled', false)) {
            throw new RuntimeException('OMC Cloud media archive is disabled.');
        }

        if (! $this->cloud->configured()) {
            throw new RuntimeException('OMC Cloud archive is not configured.');
        }

        $lock = Cache::lock('media-archive:file:'.sha1($archive->original_path), 900);

        return $lock->block(1, function () use ($archive): int {
            $archive->refresh();
            $location = MediaStorage::find($archive->original_path);

            if ($location === null) {
                return 0;
            }

            $absolutePath = $location['absolute_path'];
            $sha256 = hash_file('sha256', $absolutePath);
            $size = filesize($absolutePath);

            if ($sha256 === false || $size === false) {
                throw new RuntimeException('Не вдалося прочитати локальний медіафайл.');
            }

            $remotePath = $archive->sha256 === $sha256 && filled($archive->remote_path)
                ? $archive->remote_path
                : $this->remotePath($archive->original_path, $sha256);
            $remoteVerified = false;

            try {
                $archive->forceFill([
                    'source_disk' => $location['disk'],
                    'remote_path' => $remotePath,
                    'sha256' => $sha256,
                    'size' => $size,
                    'mime_type' => mime_content_type($absolutePath) ?: 'application/octet-stream',
                    'status' => MediaArchive::STATUS_UPLOADING,
                    'attempts' => $archive->attempts + 1,
                    'last_error' => null,
                ])->save();

                $remote = $this->cloud->upload($remotePath, $absolutePath, $sha256, (int) $size);

                $archive->forceFill([
                    'remote_path' => $remote['path'] ?? $remotePath,
                    'remote_file_id' => $remote['id'] ?? null,
                    'remote_revision_id' => $remote['revision_id'] ?? null,
                    'scan_status' => $remote['scan_status'] ?? null,
                    'status' => MediaArchive::STATUS_UPLOADED,
                    'uploaded_at' => now(),
                ])->save();

                $verification = $this->cloud->verify($archive->fresh());
                $verified = ($verification['verified'] ?? false) === true
                    && ($verification['scan_status'] ?? null) === 'clean'
                    && hash_equals($sha256, (string) ($verification['sha256'] ?? ''))
                    && (int) ($verification['size'] ?? -1) === (int) $size;

                if (! $verified) {
                    throw new RuntimeException('Віддалена копія не пройшла перевірку цілісності або антивірусну перевірку.');
                }

                $archive->forceFill([
                    'status' => MediaArchive::STATUS_VERIFIED,
                    'scan_status' => 'clean',
                    'verified_at' => now(),
                ])->save();
                $remoteVerified = true;

                $released = $this->deleteMatchingLocalCopies($archive->original_path, $sha256);

                $archive->forceFill([
                    'status' => MediaArchive::STATUS_ARCHIVED,
                    'archived_at' => now(),
                    'last_error' => null,
                ])->save();

                return $released;
            } catch (Throwable $exception) {
                $retryMinutes = min(24 * 60, 15 * (2 ** min(6, max(0, $archive->attempts - 1))));
                $archive->forceFill([
                    // Once the verified state was committed, never downgrade
                    // it after a local-delete/final-state error. The reader can
                    // then still serve the intact remote copy.
                    'status' => $remoteVerified
                        ? MediaArchive::STATUS_VERIFIED
                        : MediaArchive::STATUS_FAILED,
                    'last_error' => mb_substr($exception->getMessage(), 0, 2000),
                    'eligible_at' => now()->addMinutes($retryMinutes),
                ])->save();

                throw $exception;
            }
        });
    }

    private function remotePath(string $originalPath, string $sha256): string
    {
        $root = trim((string) config('media_archive.remote_root', 'omc-crm-archive'), '/');
        $name = preg_replace('/[^A-Za-z0-9._-]+/', '-', basename($originalPath)) ?: 'media';

        return trim(sprintf('%s/%s/%s-%s', $root, now()->format('Y/m'), $sha256, $name), '/');
    }

    private function deleteMatchingLocalCopies(string $path, string $sha256): int
    {
        $verifiedCopies = [];

        foreach (MediaStorage::locations($path) as $location) {
            $disk = Storage::disk($location['disk']);

            if (! $disk->exists($location['path'])) {
                continue;
            }

            $absolutePath = $disk->path($location['path']);
            $currentHash = is_file($absolutePath) ? hash_file('sha256', $absolutePath) : false;

            if ($currentHash === false || ! hash_equals($sha256, $currentHash)) {
                throw new RuntimeException('Локальний файл змінився під час архівації; його залишено на сервері.');
            }

            $verifiedCopies[] = [
                'disk' => $disk,
                'path' => $location['path'],
                'size' => (int) (filesize($absolutePath) ?: 0),
            ];
        }

        // Never remove even the first copy until every existing legacy/current
        // location has been checked against the verified remote object.
        $released = 0;

        foreach ($verifiedCopies as $copy) {
            if (! $copy['disk']->delete($copy['path'])) {
                throw new RuntimeException('Перевірену копію створено, але локальний файл не вдалося видалити.');
            }

            $released += $copy['size'];
        }

        return $released;
    }
}
