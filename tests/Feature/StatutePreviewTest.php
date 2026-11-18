<?php

namespace Tests\Feature;

use App\Models\Statute;
use App\Support\MediaUrl;
use App\Support\StatutePreview;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatutePreviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_current_statute_uses_the_a4_preview_and_keeps_the_original_downloadable(): void
    {
        $statute = Statute::query()->create([
            'title' => 'Статут',
            'file' => StatutePreview::SOURCE_PATH,
        ]);
        $previewUrl = StatutePreview::url($statute->file);
        $originalUrl = MediaUrl::storageVersioned($statute->file);

        $this->assertNotNull($previewUrl);

        $this->get('/')
            ->assertOk()
            ->assertSee('src="'.$previewUrl.'#view=FitH&toolbar=1&navpanes=0"', false)
            ->assertSee('href="'.$previewUrl.'"', false)
            ->assertDontSee('src="'.$originalUrl.'#view=FitH', false)
            ->assertSee('href="'.$originalUrl.'" target="_blank" rel="noopener" download', false)
            ->assertSee('Завантажити оригінал PDF');

        $this->assertSame(StatutePreview::SOURCE_PATH, $statute->fresh()->file);
    }

    public function test_other_statutes_fall_back_to_their_own_file_without_reusing_the_preview(): void
    {
        $statute = Statute::query()->create([
            'title' => 'Інший статут',
            'file' => 'statutes/another.pdf',
        ]);
        $originalUrl = MediaUrl::storageVersioned($statute->file);

        $this->assertNull(StatutePreview::url($statute->file));

        $this->get('/')
            ->assertOk()
            ->assertSee('src="'.$originalUrl.'#view=FitH&toolbar=1&navpanes=0"', false)
            ->assertSee('href="'.$originalUrl.'"', false)
            ->assertDontSee(StatutePreview::PREVIEW_PATH, false)
            ->assertDontSee('Завантажити оригінал PDF');
    }

    public function test_preview_asset_hash_and_touch_cta_are_regression_guarded(): void
    {
        $previewPath = public_path(StatutePreview::PREVIEW_PATH);
        $view = file_get_contents(resource_path('views/statuts/status.blade.php'));

        $this->assertFileExists($previewPath);
        $this->assertSame(
            StatutePreview::PREVIEW_SHA256,
            strtoupper(hash_file('sha256', $previewPath)),
        );
        $this->assertStringContainsString('@media (hover:none), (pointer:coarse)', $view);
        $this->assertStringContainsString('opacity:1;', $view);
        $this->assertStringContainsString('transform:translateY(0);', $view);
    }
}
