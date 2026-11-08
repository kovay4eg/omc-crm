<?php

namespace Tests\Unit;

use App\Models\MediaArchive;
use App\Services\OmcCloudArchiveClient;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OmcCloudArchiveClientTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('cache.default', 'array');
        config()->set('media_archive.enabled', true);
        config()->set('media_archive.base_url', 'https://cloud.example.test');
        config()->set('media_archive.login', 'archive-bot');
        config()->set('media_archive.password', 'test-only-password');
        config()->set('media_archive.verify_tls', true);
        Cache::flush();
    }

    public function test_it_uploads_a_small_file_with_cookie_and_csrf_authentication(): void
    {
        $bytes = 'verified archive payload';
        $hash = hash('sha256', $bytes);
        $path = tempnam(sys_get_temp_dir(), 'omc-archive-test-');
        file_put_contents($path, $bytes);

        Http::fakeSequence()
            ->push(['csrf' => 'csrf-token'], 200, ['Set-Cookie' => 'omc_session=abc123; HttpOnly; Path=/'])
            ->push([], 200)
            ->push([
                'id' => '11111111-1111-1111-1111-111111111111',
                'revision_id' => '22222222-2222-2222-2222-222222222222',
                'sha256' => $hash,
                'size' => strlen($bytes),
                'scan_status' => 'pending',
            ], 201);

        try {
            $result = app(OmcCloudArchiveClient::class)->upload(
                'omc-crm-archive/2026/11/'.$hash.'.bin',
                $path,
                $hash,
                strlen($bytes),
            );
        } finally {
            @unlink($path);
        }

        $this->assertSame($hash, $result['sha256']);
        $this->assertSame(strlen($bytes), $result['size']);
        $this->assertSame('pending', $result['scan_status']);
        Http::assertSentCount(3);
        Http::assertSent(fn ($request): bool => $request->url() === 'https://cloud.example.test/api/files'
            && $request->hasHeader('Cookie', 'omc_session=abc123')
            && $request->hasHeader('X-CSRF-Token', 'csrf-token'));
    }

    public function test_reads_remain_configured_when_new_archiving_is_disabled(): void
    {
        config()->set('media_archive.enabled', false);

        $this->assertTrue(app(OmcCloudArchiveClient::class)->configured());
    }

    public function test_it_uses_resumable_chunks_for_files_above_the_small_upload_limit(): void
    {
        config()->set('media_archive.small_upload_bytes', 4);
        $bytes = 'abcdefgh';
        $hash = hash('sha256', $bytes);
        $path = tempnam(sys_get_temp_dir(), 'omc-archive-test-');
        file_put_contents($path, $bytes);
        $fileId = '11111111-1111-4111-8111-111111111111';
        $uploadId = '22222222-2222-4222-8222-222222222222';

        Http::fakeSequence()
            ->push(['csrf' => 'csrf-token'], 200, ['Set-Cookie' => 'omc_session=abc123; HttpOnly; Path=/'])
            ->push([], 200)
            ->push(['chunk_size' => 4], 200)
            ->push(['id' => $uploadId], 201)
            ->push(['ok' => true], 200)
            ->push(['ok' => true], 200)
            ->push([
                'id' => $fileId,
                'revision_id' => null,
                'path' => 'omc-crm-archive/large.bin',
                'sha256' => $hash,
                'size' => strlen($bytes),
                'scan_status' => 'pending',
            ], 201);

        try {
            $result = app(OmcCloudArchiveClient::class)->upload(
                'omc-crm-archive/large.bin',
                $path,
                $hash,
                strlen($bytes),
            );
        } finally {
            @unlink($path);
        }

        $this->assertSame($fileId, $result['id']);
        Http::assertSent(fn ($request): bool => str_contains($request->url(), '/api/uploads/'.$uploadId.'/parts?offset=0')
            && $request->hasHeader('X-Content-SHA256', hash('sha256', 'abcd')));
        Http::assertSent(fn ($request): bool => str_contains($request->url(), '/api/uploads/'.$uploadId.'/parts?offset=4')
            && $request->hasHeader('X-Content-SHA256', hash('sha256', 'efgh')));
    }

    public function test_it_streams_and_rehashes_the_remote_body_before_verification(): void
    {
        $bytes = str_repeat('archive-body-', 1000);
        $hash = hash('sha256', $bytes);
        $fileId = '11111111-1111-1111-1111-111111111111';

        Http::fakeSequence()
            ->push(['csrf' => 'csrf-token'], 200, ['Set-Cookie' => 'omc_session=abc123; HttpOnly; Path=/'])
            ->push([[
                'id' => $fileId,
                'path' => 'omc-crm-archive/file.bin',
                'sha256' => $hash,
                'size' => strlen($bytes),
                'scan_status' => 'clean',
            ]], 200)
            ->push($bytes, 200, [
                'Content-Length' => (string) strlen($bytes),
                'X-Content-SHA256' => $hash,
                'X-OMC-Scan-Status' => 'clean',
            ]);

        $archive = new MediaArchive([
            'remote_file_id' => $fileId,
            'sha256' => $hash,
            'size' => strlen($bytes),
        ]);

        $result = app(OmcCloudArchiveClient::class)->verify($archive);

        $this->assertTrue($result['verified']);
        $this->assertSame($hash, $result['sha256']);
        $this->assertSame(strlen($bytes), $result['size']);
    }

    public function test_it_never_marks_a_mismatched_remote_body_as_verified(): void
    {
        $expected = 'expected body';
        $actual = 'tampered body';
        $hash = hash('sha256', $expected);
        $fileId = '11111111-1111-1111-1111-111111111111';

        Http::fakeSequence()
            ->push(['csrf' => 'csrf-token'], 200, ['Set-Cookie' => 'omc_session=abc123; HttpOnly; Path=/'])
            ->push([[
                'id' => $fileId,
                'sha256' => $hash,
                'size' => strlen($expected),
                'scan_status' => 'clean',
            ]], 200)
            ->push($actual, 200, [
                'Content-Length' => (string) strlen($actual),
                'X-Content-SHA256' => $hash,
                'X-OMC-Scan-Status' => 'clean',
            ]);

        $archive = new MediaArchive([
            'remote_file_id' => $fileId,
            'sha256' => $hash,
            'size' => strlen($expected),
        ]);

        $result = app(OmcCloudArchiveClient::class)->verify($archive);

        $this->assertFalse($result['verified']);
        $this->assertSame(hash('sha256', $actual), $result['sha256']);
    }
}
