<?php

namespace App\Contracts;

use App\Models\MediaArchive;
use Illuminate\Http\Client\Response;

interface CloudArchiveClient
{
    public function configured(): bool;

    /**
     * @return array{id: string, revision_id: string|null, path: string, sha256: string, size: int, scan_status: string}
     */
    public function upload(string $remotePath, string $absolutePath, string $sha256, int $size): array;

    /**
     * Performs an authenticated, streamed download and verifies the complete
     * remote body, not only the metadata returned by the upload endpoint.
     *
     * @return array{verified: bool, scan_status: string, sha256: string|null, size: int|null}
     */
    public function verify(MediaArchive $archive): array;

    /**
     * The response body is a streamed PSR-7 body. Consumers must read/close it.
     */
    public function download(MediaArchive $archive): Response;
}
