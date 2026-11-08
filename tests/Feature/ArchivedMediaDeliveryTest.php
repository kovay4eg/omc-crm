<?php

namespace Tests\Feature;

use App\Contracts\CloudArchiveClient;
use App\Models\MediaArchive;
use App\Support\MediaStorage;
use App\Support\MediaUrl;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Response as HttpResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArchivedMediaDeliveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_managed_local_media_is_served_through_the_controlled_route(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('events/poster.webp', 'local-poster');

        $response = $this->get('/media/events/poster.webp')->assertOk();

        $this->assertStringContainsString('max-age=86400', (string) $response->headers->get('Cache-Control'));
        $this->assertSame('local-poster', file_get_contents($response->baseResponse->getFile()->getPathname()));

        $this->assertSame(url('/media/events/poster.webp'), MediaUrl::storage('events/poster.webp'));
        $this->get('/media/unmanaged/private.txt')->assertNotFound();
    }

    public function test_legacy_storage_url_uses_the_same_safe_media_delivery(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('events/legacy.webp', 'legacy-poster');

        $response = $this->get('/storage/events/legacy.webp')->assertOk();

        $this->assertSame('legacy-poster', file_get_contents($response->baseResponse->getFile()->getPathname()));
        $this->get('/storage/unmanaged/private.txt')->assertNotFound();
    }

    public function test_verified_archived_media_is_streamed_when_the_local_file_is_absent(): void
    {
        Storage::fake('public');
        $contents = 'archived-poster';
        $sha256 = hash('sha256', $contents);
        $archive = MediaArchive::query()->create([
            'original_path' => 'events/archived.webp',
            'source_disk' => 'public',
            'remote_path' => 'omc-crm/events/archived.webp',
            'remote_file_id' => '11111111-1111-4111-8111-111111111111',
            'sha256' => $sha256,
            'size' => strlen($contents),
            'mime_type' => 'image/webp',
            'status' => MediaArchive::STATUS_ARCHIVED,
            'scan_status' => 'clean',
            'verified_at' => now(),
        ]);

        Http::fake([
            'archive.test/*' => Http::response($contents, 200, [
                'Content-Type' => 'image/webp',
                'Content-Length' => (string) strlen($contents),
                'X-Content-SHA256' => $sha256,
                'X-OMC-Scan-Status' => 'clean',
            ]),
        ]);
        $this->app->instance(CloudArchiveClient::class, $this->archiveClient(Http::get('https://archive.test/file')));

        $response = $this->get('/media/events/archived.webp?v='.$sha256)
            ->assertOk()
            ->assertHeader('Content-Type', 'image/webp')
            ->assertHeader('ETag', '"'.$sha256.'"')
            ->assertStreamedContent($contents);

        $this->assertStringContainsString('max-age=31536000', (string) $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('immutable', (string) $response->headers->get('Cache-Control'));

        $details = MediaStorage::uploadedFileDetails('events/archived.webp', null);

        $this->assertSame(strlen($contents), $details['size']);
        $this->assertSame('image/webp', $details['type']);
        $this->assertStringContainsString('/media/events/archived.webp?v=', $details['url']);
        $this->assertNotNull($archive->refresh()->last_accessed_at);
    }

    public function test_legacy_storage_url_streams_a_verified_archive_after_local_cleanup(): void
    {
        Storage::fake('public');
        $contents = 'archived-legacy-poster';
        $sha256 = hash('sha256', $contents);
        MediaArchive::query()->create([
            'original_path' => 'events/legacy-archived.webp',
            'source_disk' => 'public',
            'remote_path' => 'omc-crm/events/legacy-archived.webp',
            'remote_file_id' => '33333333-3333-4333-8333-333333333333',
            'sha256' => $sha256,
            'size' => strlen($contents),
            'mime_type' => 'image/webp',
            'status' => MediaArchive::STATUS_ARCHIVED,
            'scan_status' => 'clean',
            'verified_at' => now(),
        ]);

        Http::fake([
            'archive.test/*' => Http::response($contents, 200, [
                'Content-Type' => 'image/webp',
                'Content-Length' => (string) strlen($contents),
                'X-Content-SHA256' => $sha256,
                'X-OMC-Scan-Status' => 'clean',
            ]),
        ]);
        $this->app->instance(CloudArchiveClient::class, $this->archiveClient(Http::get('https://archive.test/file')));

        $this->get('/storage/events/legacy-archived.webp')
            ->assertOk()
            ->assertHeader('ETag', '"'.$sha256.'"')
            ->assertStreamedContent($contents);
    }

    public function test_remote_media_is_rejected_when_the_cloud_hash_does_not_match(): void
    {
        $contents = 'tampered';
        $archive = MediaArchive::query()->create([
            'original_path' => 'events/archived.webp',
            'source_disk' => 'public',
            'remote_path' => 'omc-crm/events/archived.webp',
            'remote_file_id' => '22222222-2222-4222-8222-222222222222',
            'sha256' => str_repeat('a', 64),
            'size' => strlen($contents),
            'mime_type' => 'image/webp',
            'status' => MediaArchive::STATUS_ARCHIVED,
            'scan_status' => 'clean',
            'verified_at' => now(),
        ]);

        Http::fake([
            'archive.test/*' => Http::response($contents, 200, [
                'Content-Length' => (string) strlen($contents),
                'X-Content-SHA256' => hash('sha256', $contents),
                'X-OMC-Scan-Status' => 'clean',
            ]),
        ]);
        $this->app->instance(CloudArchiveClient::class, $this->archiveClient(Http::get('https://archive.test/file')));

        $this->get('/media/events/archived.webp')->assertServiceUnavailable();
        $archive->refresh();

        $this->assertNull($archive->last_accessed_at);
        $this->assertSame(MediaArchive::STATUS_FAILED, $archive->status);
        $this->assertSame('integrity_failed', $archive->scan_status);
    }

    public function test_remote_body_is_fully_verified_before_any_bytes_are_sent(): void
    {
        $expected = 'expected!';
        $tampered = 'tampered!';
        $sha256 = hash('sha256', $expected);
        $archive = MediaArchive::query()->create([
            'original_path' => 'events/body-mismatch.webp',
            'source_disk' => 'public',
            'remote_path' => 'omc-crm/events/body-mismatch.webp',
            'remote_file_id' => '44444444-4444-4444-8444-444444444444',
            'sha256' => $sha256,
            'size' => strlen($expected),
            'mime_type' => 'image/webp',
            'status' => MediaArchive::STATUS_ARCHIVED,
            'scan_status' => 'clean',
            'verified_at' => now(),
        ]);

        Http::fake([
            'archive.test/*' => Http::response($tampered, 200, [
                'Content-Type' => 'image/webp',
                'Content-Length' => (string) strlen($tampered),
                'X-Content-SHA256' => $sha256,
                'X-OMC-Scan-Status' => 'clean',
            ]),
        ]);
        $this->app->instance(CloudArchiveClient::class, $this->archiveClient(Http::get('https://archive.test/file')));

        $this->get('/media/events/body-mismatch.webp')
            ->assertServiceUnavailable()
            ->assertDontSee($tampered);

        $archive->refresh();

        $this->assertSame(MediaArchive::STATUS_FAILED, $archive->status);
        $this->assertSame('integrity_failed', $archive->scan_status);
        $this->assertNull($archive->last_accessed_at);
    }

    public function test_matching_if_none_match_returns_304_without_cloud_download(): void
    {
        $sha256 = hash('sha256', 'cached-content');
        MediaArchive::query()->create([
            'original_path' => 'events/cached.webp',
            'source_disk' => 'public',
            'remote_path' => 'omc-crm/events/cached.webp',
            'remote_file_id' => '55555555-5555-4555-8555-555555555555',
            'sha256' => $sha256,
            'size' => 14,
            'mime_type' => 'image/webp',
            'status' => MediaArchive::STATUS_ARCHIVED,
            'scan_status' => 'clean',
            'verified_at' => now(),
        ]);

        $client = new class implements CloudArchiveClient
        {
            public int $downloads = 0;

            public function configured(): bool
            {
                return true;
            }

            public function upload(string $remotePath, string $absolutePath, string $sha256, int $size): array
            {
                throw new \LogicException('Upload is not used by delivery tests.');
            }

            public function verify(MediaArchive $archive): array
            {
                throw new \LogicException('Verify is not used by delivery tests.');
            }

            public function download(MediaArchive $archive): HttpResponse
            {
                $this->downloads++;

                throw new \LogicException('A conditional cache hit must not reach OMC Cloud.');
            }
        };
        $this->app->instance(CloudArchiveClient::class, $client);

        $this->withHeader('If-None-Match', 'W/"'.$sha256.'"')
            ->get('/media/events/cached.webp')
            ->assertStatus(304)
            ->assertHeader('ETag', '"'.$sha256.'"');

        $this->assertSame(0, $client->downloads);
    }

    public function test_last_access_timestamp_is_throttled(): void
    {
        $contents = 'archived-poster';
        $sha256 = hash('sha256', $contents);
        $lastAccessed = now()->subHour()->startOfSecond();
        $archive = MediaArchive::query()->create([
            'original_path' => 'events/recently-accessed.webp',
            'source_disk' => 'public',
            'remote_path' => 'omc-crm/events/recently-accessed.webp',
            'remote_file_id' => '66666666-6666-4666-8666-666666666666',
            'sha256' => $sha256,
            'size' => strlen($contents),
            'mime_type' => 'image/webp',
            'status' => MediaArchive::STATUS_ARCHIVED,
            'scan_status' => 'clean',
            'verified_at' => now(),
            'last_accessed_at' => $lastAccessed,
        ]);

        Http::fake([
            'archive.test/*' => Http::response($contents, 200, [
                'Content-Type' => 'image/webp',
                'Content-Length' => (string) strlen($contents),
                'X-Content-SHA256' => $sha256,
                'X-OMC-Scan-Status' => 'clean',
            ]),
        ]);
        $this->app->instance(CloudArchiveClient::class, $this->archiveClient(Http::get('https://archive.test/file')));

        $this->get('/media/events/recently-accessed.webp')
            ->assertOk()
            ->assertStreamedContent($contents);

        $this->assertTrue($archive->refresh()->last_accessed_at->equalTo($lastAccessed));
    }

    private function archiveClient(HttpResponse $response): CloudArchiveClient
    {
        return new class($response) implements CloudArchiveClient
        {
            public function __construct(private readonly HttpResponse $response) {}

            public function configured(): bool
            {
                return true;
            }

            public function upload(string $remotePath, string $absolutePath, string $sha256, int $size): array
            {
                throw new \LogicException('Upload is not used by delivery tests.');
            }

            public function verify(MediaArchive $archive): array
            {
                throw new \LogicException('Verify is not used by delivery tests.');
            }

            public function download(MediaArchive $archive): HttpResponse
            {
                return $this->response;
            }
        };
    }
}
