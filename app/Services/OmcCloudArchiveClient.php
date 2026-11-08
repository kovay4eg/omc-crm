<?php

namespace App\Services;

use App\Contracts\CloudArchiveClient;
use App\Models\MediaArchive;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

final class OmcCloudArchiveClient implements CloudArchiveClient
{
    private const SESSION_CACHE_PREFIX = 'omc-media-archive:session:';

    public function configured(): bool
    {
        // The write toggle is intentionally not part of this check: disabling
        // new archival must not make previously archived public media unreadable.
        $baseUrl = (string) config('media_archive.base_url');

        return filter_var($baseUrl, FILTER_VALIDATE_URL) !== false
            && strtolower((string) parse_url($baseUrl, PHP_URL_SCHEME)) === 'https'
            && trim((string) config('media_archive.login')) !== ''
            && (string) config('media_archive.password') !== '';
    }

    public function upload(string $remotePath, string $absolutePath, string $sha256, int $size): array
    {
        $this->assertConfigured();
        $remotePath = $this->normalizeRemotePath($remotePath);
        $sha256 = strtolower(trim($sha256));

        if (! is_file($absolutePath) || ! is_readable($absolutePath)) {
            throw new RuntimeException('The local media file is unavailable for archiving.');
        }

        if ($size < 0 || filesize($absolutePath) !== $size) {
            throw new RuntimeException('The local media size changed before archiving.');
        }

        if (! preg_match('/^[a-f0-9]{64}$/', $sha256) || hash_file('sha256', $absolutePath) !== $sha256) {
            throw new RuntimeException('The local media checksum changed before archiving.');
        }

        $existing = $this->findRemoteByPath($remotePath);

        if ($existing !== null) {
            return $this->normalizeFile($existing, $remotePath, $sha256, $size);
        }

        $smallLimit = max(1, (int) config('media_archive.small_upload_bytes', 24 * 1024 * 1024));

        try {
            $file = $size <= $smallLimit
                ? $this->uploadSmall($remotePath, $absolutePath)
                : $this->uploadResumable($remotePath, $absolutePath, $size);
        } catch (RuntimeException $exception) {
            // A retry after a lost response can encounter an already-created
            // file. Resolve it by the immutable hash-derived path.
            $existing = $this->findRemoteByPath($remotePath);

            if ($existing === null) {
                throw $exception;
            }

            $file = $existing;
        }

        return $this->normalizeFile($file, $remotePath, $sha256, $size);
    }

    public function verify(MediaArchive $archive): array
    {
        $this->assertConfigured();
        $expectedHash = strtolower((string) $archive->sha256);
        $expectedSize = $archive->size === null ? null : (int) $archive->size;

        if (! preg_match('/^[a-f0-9]{64}$/', $expectedHash) || ! $archive->remote_file_id) {
            throw new RuntimeException('The archive record has no verifiable remote identity.');
        }

        $remote = $this->findRemoteById((string) $archive->remote_file_id);

        if ($remote === null) {
            throw new RuntimeException('The archived OMC Cloud file no longer exists.');
        }

        $scanStatus = strtolower((string) ($remote['scan_status'] ?? 'pending')) ?: 'pending';

        if ($scanStatus !== 'clean') {
            return [
                'verified' => false,
                'scan_status' => $scanStatus,
                'sha256' => isset($remote['sha256']) ? strtolower((string) $remote['sha256']) : null,
                'size' => isset($remote['size']) ? (int) $remote['size'] : null,
            ];
        }

        $response = $this->download($archive);

        if (! $response->successful()) {
            $this->closeResponseBody($response);

            return [
                'verified' => false,
                'scan_status' => $response->status() === 423 ? 'pending' : 'unavailable',
                'sha256' => null,
                'size' => null,
            ];
        }

        $headerScan = strtolower((string) $response->header('X-OMC-Scan-Status'));
        $headerHash = strtolower((string) $response->header('X-Content-SHA256'));
        $headerSize = $response->header('Content-Length');
        $body = $response->toPsrResponse()->getBody();
        $hash = hash_init('sha256');
        $bytes = 0;

        try {
            while (! $body->eof()) {
                $chunk = $body->read(1024 * 1024);

                if ($chunk === '') {
                    continue;
                }

                hash_update($hash, $chunk);
                $bytes += strlen($chunk);
            }
        } finally {
            $body->close();
        }

        $bodyHash = hash_final($hash);
        $verified = $headerScan === 'clean'
            && hash_equals($expectedHash, $headerHash)
            && hash_equals($expectedHash, $bodyHash)
            && ($expectedSize === null || $bytes === $expectedSize)
            && ($headerSize === null || (int) $headerSize === $bytes);

        return [
            'verified' => $verified,
            'scan_status' => $headerScan ?: $scanStatus,
            'sha256' => $bodyHash,
            'size' => $bytes,
        ];
    }

    public function download(MediaArchive $archive): Response
    {
        $this->assertConfigured();

        if (! $archive->remote_file_id) {
            throw new RuntimeException('The archive record has no remote file identifier.');
        }

        return $this->authenticated(fn (PendingRequest $request): Response => $request
            ->withOptions(['stream' => true])
            ->get('/api/files/'.rawurlencode((string) $archive->remote_file_id)));
    }

    /**
     * @return array<string, mixed>
     */
    private function uploadSmall(string $remotePath, string $absolutePath): array
    {
        $response = $this->authenticated(function (PendingRequest $request) use ($remotePath, $absolutePath): Response {
            $stream = fopen($absolutePath, 'rb');

            if ($stream === false) {
                throw new RuntimeException('Unable to open the local media file.');
            }

            try {
                return $request
                    ->withHeader('Content-Type', 'application/octet-stream')
                    ->send('POST', '/api/files', [
                        'query' => [
                            'path' => $remotePath,
                            'versioned' => '0',
                            'comment' => 'OMC CRM cold-storage archive',
                        ],
                        'body' => $stream,
                    ]);
            } finally {
                fclose($stream);
            }
        });

        $this->assertSuccessful($response);

        return $this->jsonObject($response);
    }

    /**
     * @return array<string, mixed>
     */
    private function uploadResumable(string $remotePath, string $absolutePath, int $size): array
    {
        $configResponse = $this->authenticated(fn (PendingRequest $request): Response => $request->get('/api/uploads/config'));
        $this->assertSuccessful($configResponse);
        $serverConfig = $this->jsonObject($configResponse);
        $chunkSize = max(1, (int) ($serverConfig['chunk_size'] ?? config('media_archive.chunk_bytes', 4 * 1024 * 1024)));

        $begin = $this->authenticated(fn (PendingRequest $request): Response => $request->asJson()->post('/api/uploads', [
            'size' => $size,
            'filePath' => $remotePath,
            'versioned' => false,
            'comment' => 'OMC CRM cold-storage archive',
        ]));
        $this->assertSuccessful($begin);
        $session = $this->jsonObject($begin);
        $uploadId = (string) ($session['id'] ?? '');

        if (! preg_match('/^[a-f0-9-]{36}$/', $uploadId)) {
            throw new RuntimeException('OMC Cloud returned an invalid upload session.');
        }

        $completed = false;

        try {
            $handle = fopen($absolutePath, 'rb');

            if ($handle === false) {
                throw new RuntimeException('Unable to open the local media file.');
            }

            try {
                for ($offset = 0; $offset < $size; $offset += $chunkSize) {
                    $length = min($chunkSize, $size - $offset);
                    $chunk = $this->readExactly($handle, $length);
                    $chunkHash = hash('sha256', $chunk);
                    $part = $this->authenticated(fn (PendingRequest $request): Response => $request
                        ->withHeaders([
                            'Content-Type' => 'application/octet-stream',
                            'X-Content-SHA256' => $chunkHash,
                        ])
                        ->send('PUT', '/api/uploads/'.rawurlencode($uploadId).'/parts', [
                            'query' => ['offset' => $offset],
                            'body' => $chunk,
                        ]));
                    $this->assertSuccessful($part);
                }
            } finally {
                fclose($handle);
            }

            $finish = $this->authenticated(fn (PendingRequest $request): Response => $request
                ->withHeader('Content-Type', 'application/json')
                ->send('POST', '/api/uploads/'.rawurlencode($uploadId).'/complete', ['body' => '{}']));
            $this->assertSuccessful($finish);
            $completed = true;

            return $this->jsonObject($finish);
        } finally {
            if (! $completed) {
                try {
                    $this->authenticated(fn (PendingRequest $request): Response => $request->delete('/api/uploads/'.rawurlencode($uploadId)));
                } catch (Throwable) {
                    // The server expires unfinished sessions after 24 hours.
                }
            }
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    private function findRemoteByPath(string $path): ?array
    {
        foreach ($this->listRemoteFiles() as $file) {
            if (($file['path'] ?? null) === $path) {
                return $file;
            }
        }

        return null;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function findRemoteById(string $id): ?array
    {
        foreach ($this->listRemoteFiles() as $file) {
            if (($file['id'] ?? null) === $id) {
                return $file;
            }
        }

        return null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function listRemoteFiles(): array
    {
        $response = $this->authenticated(fn (PendingRequest $request): Response => $request->get('/api/files'));
        $this->assertSuccessful($response);
        $payload = $response->json();

        if (is_array($payload) && isset($payload['items']) && is_array($payload['items'])) {
            $payload = $payload['items'];
        }

        if (! is_array($payload)) {
            throw new RuntimeException('OMC Cloud returned an invalid file list.');
        }

        return array_values(array_filter($payload, 'is_array'));
    }

    /**
     * @param  array<string, mixed>  $file
     * @return array{id: string, revision_id: string|null, path: string, sha256: string, size: int, scan_status: string}
     */
    private function normalizeFile(array $file, string $remotePath, string $sha256, int $size): array
    {
        $id = (string) ($file['id'] ?? '');
        $actualHash = strtolower((string) ($file['sha256'] ?? ''));
        $actualSize = isset($file['size']) ? (int) $file['size'] : -1;

        if ($id === '' || ! hash_equals($sha256, $actualHash) || $actualSize !== $size) {
            throw new RuntimeException('OMC Cloud upload metadata did not match the local media file.');
        }

        return [
            'id' => $id,
            'revision_id' => isset($file['revision_id'])
                ? (string) $file['revision_id']
                : (isset($file['current_revision']) ? (string) $file['current_revision'] : null),
            'path' => (string) ($file['path'] ?? $remotePath),
            'sha256' => $actualHash,
            'size' => $actualSize,
            'scan_status' => strtolower((string) ($file['scan_status'] ?? 'pending')) ?: 'pending',
        ];
    }

    private function authenticated(callable $operation): Response
    {
        $session = $this->session();
        $response = $operation($this->request($session));

        if ($response->status() !== 401) {
            return $response;
        }

        $this->closeResponseBody($response);
        Cache::forget($this->sessionCacheKey());
        $session = $this->session();

        return $operation($this->request($session));
    }

    /**
     * @param  array{cookie: string, csrf: string}  $session
     */
    private function request(array $session): PendingRequest
    {
        return $this->baseRequest()->withHeaders([
            'Cookie' => 'omc_session='.$session['cookie'],
            'X-CSRF-Token' => $session['csrf'],
        ]);
    }

    private function baseRequest(): PendingRequest
    {
        return Http::baseUrl(rtrim((string) config('media_archive.base_url'), '/'))
            ->acceptJson()
            ->timeout(max(1, (int) config('media_archive.timeout', 120)))
            ->connectTimeout(max(1, (int) config('media_archive.connect_timeout', 15)))
            ->withUserAgent('OMC-CRM-Media-Archive/1.0')
            ->withOptions(['verify' => (bool) config('media_archive.verify_tls', true)]);
    }

    /**
     * @return array{cookie: string, csrf: string}
     */
    private function session(): array
    {
        $cached = Cache::get($this->sessionCacheKey());

        if (is_string($cached) && $cached !== '') {
            try {
                $decoded = json_decode(Crypt::decryptString($cached), true, flags: JSON_THROW_ON_ERROR);

                if (is_array($decoded) && isset($decoded['cookie'], $decoded['csrf'])) {
                    return ['cookie' => (string) $decoded['cookie'], 'csrf' => (string) $decoded['csrf']];
                }
            } catch (Throwable) {
                Cache::forget($this->sessionCacheKey());
            }
        }

        $response = $this->baseRequest()->asJson()->post('/api/login', [
            'login' => (string) config('media_archive.login'),
            'password' => (string) config('media_archive.password'),
        ]);

        if (! $response->successful()) {
            throw new RuntimeException('OMC Cloud archive authentication failed.');
        }

        $csrf = (string) $response->json('csrf', '');
        $setCookie = (string) $response->header('Set-Cookie');
        preg_match('/(?:^|[,;]\s*)omc_session=([a-f0-9]+)/i', $setCookie, $matches);
        $cookie = (string) ($matches[1] ?? '');

        if ($csrf === '' || $cookie === '') {
            throw new RuntimeException('The OMC Cloud archive account requires an interactive login or MFA.');
        }

        $session = ['cookie' => $cookie, 'csrf' => $csrf];
        $ttl = min(25200, max(60, (int) config('media_archive.session_ttl_seconds', 25200)));
        Cache::put(
            $this->sessionCacheKey(),
            Crypt::encryptString(json_encode($session, JSON_THROW_ON_ERROR)),
            now()->addSeconds($ttl),
        );

        return $session;
    }

    private function sessionCacheKey(): string
    {
        return self::SESSION_CACHE_PREFIX.sha1(
            rtrim((string) config('media_archive.base_url'), '/').'|'.strtolower((string) config('media_archive.login')),
        );
    }

    private function assertConfigured(): void
    {
        if (! $this->configured()) {
            throw new RuntimeException('OMC Cloud media archive credentials are not configured.');
        }
    }

    private function assertSuccessful(Response $response): void
    {
        if (! $response->successful()) {
            $status = $response->status();
            $this->closeResponseBody($response);

            throw new RuntimeException("OMC Cloud request failed with HTTP {$status}.");
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function jsonObject(Response $response): array
    {
        $payload = $response->json();

        if (! is_array($payload)) {
            throw new RuntimeException('OMC Cloud returned invalid JSON.');
        }

        return $payload;
    }

    /** @param resource $handle */
    private function readExactly($handle, int $length): string
    {
        $buffer = '';

        while (strlen($buffer) < $length && ! feof($handle)) {
            $chunk = fread($handle, $length - strlen($buffer));

            if ($chunk === false) {
                throw new RuntimeException('Unable to read the local media file.');
            }

            $buffer .= $chunk;
        }

        if (strlen($buffer) !== $length) {
            throw new RuntimeException('The local media file changed during upload.');
        }

        return $buffer;
    }

    private function normalizeRemotePath(string $path): string
    {
        $path = trim(str_replace('\\', '/', $path), '/');

        if ($path === '' || strlen($path) > 1024 || str_contains("/{$path}/", '/../') || str_contains("/{$path}/", '/./')) {
            throw new RuntimeException('The OMC Cloud archive path is invalid.');
        }

        return $path;
    }

    private function closeResponseBody(Response $response): void
    {
        try {
            $response->toPsrResponse()->getBody()->close();
        } catch (Throwable) {
            // There may be no streamed body (for example in an HTTP fake).
        }
    }
}
