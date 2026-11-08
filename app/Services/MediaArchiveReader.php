<?php

namespace App\Services;

use App\Contracts\CloudArchiveClient;
use App\Models\MediaArchive;
use Illuminate\Http\Client\Response as HttpResponse;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class MediaArchiveReader
{
    private const ACCESS_TOUCH_INTERVAL_HOURS = 6;

    public function __construct(private readonly CloudArchiveClient $client) {}

    public function response(string $path): BinaryFileResponse|Response
    {
        $archive = $this->archive($path);
        $headers = $this->responseHeaders($archive);

        if ($this->requestAcceptsEtag($archive->sha256)) {
            return response('', Response::HTTP_NOT_MODIFIED, $headers);
        }

        $temporaryPath = $this->downloadVerified($archive);

        $this->touchLastAccessed($archive);

        return response()
            ->file($temporaryPath, $headers)
            ->deleteFileAfterSend(true);
    }

    public function contents(string $path): ?string
    {
        $temporaryPath = null;

        try {
            $archive = $this->archive($path);
            $temporaryPath = $this->downloadVerified($archive);
            $contents = file_get_contents($temporaryPath);

            if ($contents === false) {
                throw new RuntimeException('Unable to read verified archived media from the temporary file.');
            }

            $this->touchLastAccessed($archive);

            return $contents;
        } catch (Throwable $exception) {
            report($exception);

            return null;
        } finally {
            if ($temporaryPath !== null && is_file($temporaryPath)) {
                @unlink($temporaryPath);
            }
        }
    }

    private function archive(string $path): MediaArchive
    {
        abort_unless($this->client->configured() && Schema::hasTable('media_archives'), 404);

        $archive = MediaArchive::query()
            ->where('original_path', $path)
            ->whereIn('status', [MediaArchive::STATUS_VERIFIED, MediaArchive::STATUS_ARCHIVED])
            ->latest('verified_at')
            ->first();

        abort_if($archive === null, 404);

        return $archive;
    }

    private function downloadVerified(MediaArchive $archive): string
    {
        $remote = $this->client->download($archive);

        if (! $remote->successful()) {
            $remote->toPsrResponse()->getBody()->close();
            abort(503, 'Archived media is temporarily unavailable.');
        }

        $this->verifyRemoteHeaders($archive, $remote);

        $temporaryPath = tempnam(sys_get_temp_dir(), 'omc-archive-');

        if ($temporaryPath === false) {
            abort(503, 'Unable to prepare archived media.');
        }

        $target = fopen($temporaryPath, 'wb');

        if ($target === false) {
            @unlink($temporaryPath);
            abort(503, 'Unable to prepare archived media.');
        }

        $body = $remote->toPsrResponse()->getBody();
        $hash = hash_init('sha256');
        $bytes = 0;

        try {
            while (! $body->eof()) {
                $chunk = $body->read(64 * 1024);

                if ($chunk === '') {
                    continue;
                }

                hash_update($hash, $chunk);
                $bytes += strlen($chunk);

                $remaining = $chunk;

                while ($remaining !== '') {
                    $written = fwrite($target, $remaining);

                    if ($written === false || $written === 0) {
                        throw new RuntimeException('Unable to buffer archived media safely.');
                    }

                    $remaining = substr($remaining, $written);
                }
            }
        } catch (Throwable $exception) {
            fclose($target);
            $body->close();
            @unlink($temporaryPath);

            throw $exception;
        }

        fclose($target);
        $body->close();

        $actualHash = hash_final($hash);
        $expectedSize = $archive->size === null ? null : (int) $archive->size;

        if (! hash_equals(strtolower((string) $archive->sha256), strtolower($actualHash))
            || ($expectedSize !== null && $bytes !== $expectedSize)) {
            @unlink($temporaryPath);
            $this->quarantine($archive, 'Remote archived media body failed SHA-256 or size verification.');
            abort(503, 'Archived media failed integrity verification.');
        }

        return $temporaryPath;
    }

    private function verifyRemoteHeaders(MediaArchive $archive, HttpResponse $remote): void
    {
        $scanStatus = strtolower((string) $remote->header('X-OMC-Scan-Status'));
        $remoteHash = strtolower((string) $remote->header('X-Content-SHA256'));
        $expectedHash = strtolower((string) $archive->sha256);
        $contentLength = $remote->header('Content-Length');

        if ($scanStatus !== 'clean'
            || ! hash_equals($expectedHash, $remoteHash)
            || ($archive->size !== null && $contentLength !== null && (int) $contentLength !== (int) $archive->size)) {
            $remote->toPsrResponse()->getBody()->close();
            $this->quarantine($archive, 'Remote archived media headers failed scan, SHA-256 or size verification.');
            abort(503, 'Archived media failed integrity verification.');
        }
    }

    /**
     * @return array<string, string>
     */
    private function responseHeaders(MediaArchive $archive): array
    {
        $cacheControl = request()->filled('v')
            ? 'public, max-age=31536000, immutable'
            : 'public, max-age=86400';

        $headers = [
            'Cache-Control' => $cacheControl,
            'Content-Type' => $archive->mime_type ?: 'application/octet-stream',
            'ETag' => '"'.$archive->sha256.'"',
            'X-Content-Type-Options' => 'nosniff',
        ];

        if ($archive->size !== null) {
            $headers['Content-Length'] = (string) $archive->size;
        }

        return $headers;
    }

    private function requestAcceptsEtag(string $sha256): bool
    {
        $ifNoneMatch = request()->header('If-None-Match');

        if (! is_string($ifNoneMatch) || $ifNoneMatch === '') {
            return false;
        }

        foreach (explode(',', $ifNoneMatch) as $candidate) {
            $candidate = trim($candidate);

            if ($candidate === '*') {
                return true;
            }

            if (str_starts_with($candidate, 'W/')) {
                $candidate = substr($candidate, 2);
            }

            if (hash_equals('"'.$sha256.'"', $candidate)) {
                return true;
            }
        }

        return false;
    }

    private function touchLastAccessed(MediaArchive $archive): void
    {
        if ($archive->last_accessed_at !== null
            && $archive->last_accessed_at->isAfter(now()->subHours(self::ACCESS_TOUCH_INTERVAL_HOURS))) {
            return;
        }

        $archive->forceFill(['last_accessed_at' => now()])->saveQuietly();
    }

    private function quarantine(MediaArchive $archive, string $reason): void
    {
        try {
            $archive->forceFill([
                'status' => MediaArchive::STATUS_FAILED,
                'scan_status' => 'integrity_failed',
                'last_error' => $reason,
            ])->saveQuietly();
        } catch (Throwable $exception) {
            report($exception);
        }

        report(new RuntimeException($reason));
    }
}
