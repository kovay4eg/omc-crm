<?php

namespace Tests\Feature;

use App\Contracts\CloudArchiveClient;
use App\Enums\EventStatus;
use App\Models\AppAnnouncement;
use App\Models\Department;
use App\Models\Event;
use App\Models\EventSummary;
use App\Models\EventSummaryImage;
use App\Models\MediaArchive;
use App\Models\User;
use App\Services\MediaArchiveCandidateService;
use App\Services\MediaArchiveManager;
use App\Support\MediaStorage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class MediaArchiveSafetyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        Storage::fake('local');
        config()->set('media_archive.enabled', true);
    }

    public function test_retiring_media_keeps_the_local_file_and_creates_pending_record(): void
    {
        Storage::disk('public')->put('events/replaced.webp', 'old image bytes');
        $this->app->instance(CloudArchiveClient::class, $this->cloudClient());

        app(MediaArchiveManager::class)->retire('events/replaced.webp', 'replaced', null, 2);

        Storage::disk('public')->assertExists('events/replaced.webp');
        $this->assertDatabaseHas('media_archives', [
            'original_path' => 'events/replaced.webp',
            'reason' => 'replaced',
            'status' => MediaArchive::STATUS_PENDING,
        ]);
        $this->assertTrue(MediaArchive::query()->firstOrFail()->eligible_at->isFuture());

        config()->set('media_archive.retention.orphan_grace_days', 0);
        $this->assertFalse(collect(app(MediaArchiveCandidateService::class)->candidates(20))
            ->contains(fn (array $candidate): bool => $candidate['path'] === 'events/replaced.webp'));
    }

    public function test_local_file_is_deleted_only_after_full_remote_verification(): void
    {
        Storage::disk('public')->put('events/old.webp', 'verified image bytes');
        $this->app->instance(CloudArchiveClient::class, $this->cloudClient());

        $released = app(MediaArchiveManager::class)->archiveCandidate([
            'path' => 'events/old.webp',
            'reason' => 'old_event',
            'source_type' => null,
            'source_id' => null,
        ]);

        $this->assertSame(strlen('verified image bytes'), $released);
        Storage::disk('public')->assertMissing('events/old.webp');
        $this->assertDatabaseHas('media_archives', [
            'original_path' => 'events/old.webp',
            'status' => MediaArchive::STATUS_ARCHIVED,
            'scan_status' => 'clean',
        ]);
    }

    public function test_hash_mismatch_keeps_the_local_file(): void
    {
        Storage::disk('public')->put('events/unsafe.webp', 'local bytes');
        $this->app->instance(CloudArchiveClient::class, $this->cloudClient(false));

        try {
            app(MediaArchiveManager::class)->archiveCandidate([
                'path' => 'events/unsafe.webp',
                'reason' => 'old_event',
                'source_type' => null,
                'source_id' => null,
            ]);
            $this->fail('Verification failure was expected.');
        } catch (RuntimeException) {
            // Expected: the important assertion is that the local copy stays.
        }

        Storage::disk('public')->assertExists('events/unsafe.webp');
        $this->assertDatabaseHas('media_archives', [
            'original_path' => 'events/unsafe.webp',
            'status' => MediaArchive::STATUS_FAILED,
        ]);
    }

    public function test_no_local_copy_is_deleted_when_any_legacy_copy_has_different_bytes(): void
    {
        Storage::disk('public')->put('events/duplicated.webp', 'current bytes');
        Storage::disk('local')->put('private/events/duplicated.webp', 'stale bytes');
        $this->app->instance(CloudArchiveClient::class, $this->cloudClient());

        try {
            app(MediaArchiveManager::class)->archiveCandidate([
                'path' => 'events/duplicated.webp',
                'reason' => 'old_event',
                'source_type' => null,
                'source_id' => null,
            ]);
            $this->fail('A mismatched legacy copy must prevent every local deletion.');
        } catch (RuntimeException) {
            // Expected. Both local copies must remain untouched.
        }

        Storage::disk('public')->assertExists('events/duplicated.webp');
        Storage::disk('local')->assertExists('private/events/duplicated.webp');
        $this->assertDatabaseHas('media_archives', [
            'original_path' => 'events/duplicated.webp',
            'status' => MediaArchive::STATUS_VERIFIED,
        ]);
    }

    public function test_disabled_archive_never_deletes_a_local_file(): void
    {
        config()->set('media_archive.enabled', false);
        Storage::disk('public')->put('events/disabled.webp', 'must stay local');
        $this->app->instance(CloudArchiveClient::class, $this->cloudClient());

        $this->expectException(RuntimeException::class);

        try {
            app(MediaArchiveManager::class)->archiveCandidate([
                'path' => 'events/disabled.webp',
                'reason' => 'old_event',
                'source_type' => null,
                'source_id' => null,
            ]);
        } finally {
            Storage::disk('public')->assertExists('events/disabled.webp');
        }
    }

    public function test_orphan_candidates_are_discovered_without_deleting_them(): void
    {
        config()->set('media_archive.retention.orphan_grace_days', 0);
        Storage::disk('public')->put('events/orphan.webp', 'orphan bytes');

        $candidates = app(MediaArchiveCandidateService::class)->candidates(20);

        $this->assertTrue(collect($candidates)->contains(fn (array $candidate): bool => $candidate['path'] === 'events/orphan.webp' && $candidate['reason'] === 'orphan'));
        Storage::disk('public')->assertExists('events/orphan.webp');
    }

    public function test_old_announcements_events_and_summaries_are_candidates_but_active_team_media_is_not(): void
    {
        foreach ([
            'app-announcements/expired.webp',
            'events/old.webp',
            'event-summaries/old-cover.webp',
            'event-summaries/old-gallery.webp',
            'team/departments/current.webp',
        ] as $path) {
            Storage::disk('public')->put($path, 'media-'.$path);
        }

        $user = User::factory()->create(['role' => 'content']);
        AppAnnouncement::query()->create([
            'title' => 'Старе оголошення',
            'body' => 'Архівний текст лишається у БД.',
            'image_path' => 'app-announcements/expired.webp',
            'starts_at' => now()->subDays(120),
            'expires_at' => now()->subDays(100),
            'is_active' => false,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);
        $event = Event::query()->create([
            'title' => 'Старий захід',
            'description' => 'Опис',
            'event_date' => now()->subDays(200),
            'image' => 'events/old.webp',
            'status' => EventStatus::Published->value,
            'has_registration_button' => false,
            'registration_type' => 'none',
            'show_available_slots' => true,
            'user_id' => $user->id,
        ]);
        $summary = EventSummary::query()->create([
            'event_id' => $event->id,
            'summary' => 'Підсумок лишається у БД.',
            'status' => EventSummary::STATUS_PUBLISHED,
            'user_id' => $user->id,
            'published_at' => now()->subDays(200),
            'smm_image' => 'event-summaries/old-cover.webp',
        ]);
        EventSummaryImage::query()->create([
            'event_summary_id' => $summary->id,
            'image' => 'event-summaries/old-gallery.webp',
            'sort_order' => 0,
        ]);
        Department::query()->create([
            'name' => 'Актуальний відділ',
            'photo' => 'team/departments/current.webp',
        ]);

        $this->assertDatabaseHas('event_summary_images', [
            'event_summary_id' => $summary->id,
            'image' => 'event-summaries/old-gallery.webp',
        ]);
        Storage::disk('public')->assertExists('event-summaries/old-gallery.webp');
        $this->assertSame(1, EventSummaryImage::query()->where('event_summary_id', $summary->id)->count());
        $this->assertNotNull(MediaStorage::find('event-summaries/old-gallery.webp'));
        $this->assertTrue(Schema::hasTable((new EventSummaryImage)->getTable()));

        $candidates = collect(app(MediaArchiveCandidateService::class)->candidates(50))->keyBy('path');

        $this->assertSame('old_announcement', $candidates['app-announcements/expired.webp']['reason']);
        $this->assertSame('old_event', $candidates['events/old.webp']['reason']);
        $this->assertSame('old_summary', $candidates['event-summaries/old-cover.webp']['reason']);
        $this->assertTrue(
            $candidates->has('event-summaries/old-gallery.webp'),
            'Missing gallery candidate; found: '.$candidates->keys()->implode(', '),
        );
        $this->assertSame('old_summary', $candidates['event-summaries/old-gallery.webp']['reason']);
        $this->assertFalse($candidates->has('team/departments/current.webp'));
    }

    private function cloudClient(bool $verified = true): CloudArchiveClient
    {
        return new class($verified) implements CloudArchiveClient
        {
            public function __construct(private readonly bool $verified) {}

            public function configured(): bool
            {
                return true;
            }

            public function upload(string $remotePath, string $absolutePath, string $sha256, int $size): array
            {
                return [
                    'id' => '11111111-1111-4111-8111-111111111111',
                    'revision_id' => null,
                    'path' => $remotePath,
                    'sha256' => $sha256,
                    'size' => $size,
                    'scan_status' => 'clean',
                ];
            }

            public function verify(MediaArchive $archive): array
            {
                return [
                    'verified' => $this->verified,
                    'scan_status' => 'clean',
                    'sha256' => $this->verified ? $archive->sha256 : str_repeat('0', 64),
                    'size' => $archive->size,
                ];
            }

            public function download(MediaArchive $archive): Response
            {
                throw new RuntimeException('Not used in this test.');
            }
        };
    }
}
