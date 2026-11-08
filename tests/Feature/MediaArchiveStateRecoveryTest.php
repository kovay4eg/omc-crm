<?php

namespace Tests\Feature;

use App\Models\MediaArchive;
use App\Services\DiskSpaceMonitor;
use App\Services\MediaArchiveCandidateService;
use App\Services\MediaArchivePressureState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;
use Tests\TestCase;

class MediaArchiveStateRecoveryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        Storage::fake('local');
        Cache::forget('media-archive:pressure-active');
        config()->set('media_archive.enabled', true);
        config()->set('media_archive.retention.orphan_grace_days', 365);
        config()->set('media_archive.stale_upload_minutes', 30);
    }

    public function test_scheduler_continues_while_pressure_is_between_trigger_and_recovery_thresholds(): void
    {
        app(MediaArchivePressureState::class)->activate();

        $this->mock(DiskSpaceMonitor::class, function (MockInterface $mock): void {
            $mock->shouldReceive('snapshot')->once()->andReturn([
                'path' => storage_path('app'),
                'total' => 1_000,
                'free' => 120,
                'used' => 880,
                'free_percent' => 12.0,
                'low' => false,
                'recovered' => false,
            ]);
        });

        $this->artisan('media:archive-if-low')
            ->expectsOutput('Файлів, придатних до архівації, не знайдено.')
            ->assertSuccessful();

        $this->assertTrue(app(MediaArchivePressureState::class)->active());
    }

    public function test_stale_uploading_and_uploaded_records_return_to_candidate_queue(): void
    {
        foreach (['stale-uploading.webp', 'stale-uploaded.webp', 'fresh-uploading.webp'] as $filename) {
            Storage::disk('public')->put('events/'.$filename, $filename);
        }

        $staleUploading = $this->archive('events/stale-uploading.webp', MediaArchive::STATUS_UPLOADING);
        $staleUploaded = $this->archive('events/stale-uploaded.webp', MediaArchive::STATUS_UPLOADED);
        $freshUploading = $this->archive('events/fresh-uploading.webp', MediaArchive::STATUS_UPLOADING);

        MediaArchive::query()->whereKey([$staleUploading->id, $staleUploaded->id])->update([
            'updated_at' => now()->subHour(),
        ]);
        MediaArchive::query()->whereKey($freshUploading->id)->update([
            'updated_at' => now(),
        ]);

        $candidates = collect(app(MediaArchiveCandidateService::class)->candidates(20))->pluck('path');

        $this->assertTrue($candidates->contains('events/stale-uploading.webp'));
        $this->assertTrue($candidates->contains('events/stale-uploaded.webp'));
        $this->assertFalse($candidates->contains('events/fresh-uploading.webp'));
    }

    public function test_migration_refuses_to_drop_remote_archive_metadata(): void
    {
        $archive = $this->archive('events/remote.webp', MediaArchive::STATUS_ARCHIVED);
        $archive->update([
            'remote_file_id' => '11111111-1111-4111-8111-111111111111',
        ]);

        $migration = require database_path('migrations/2026_11_08_000000_create_media_archives_table.php');

        try {
            $migration->down();
            $this->fail('Rollback must refuse to discard remote archive metadata.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('cannot be rolled back', $exception->getMessage());
        }

        $this->assertTrue(Schema::hasTable('media_archives'));
        $this->assertDatabaseHas('media_archives', ['id' => $archive->id]);
    }

    private function archive(string $path, string $status): MediaArchive
    {
        return MediaArchive::query()->create([
            'original_path' => $path,
            'source_disk' => 'public',
            'reason' => 'replaced',
            'status' => $status,
            'eligible_at' => now()->subMinute(),
        ]);
    }
}
