<?php

namespace Tests\Feature\Api\V1;

use App\Models\AppAnnouncement;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AppStateAndAnnouncementTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_admin_can_toggle_site_and_mobile_maintenance_modes(): void
    {
        $editor = User::factory()->create(['role' => 'editor']);
        Sanctum::actingAs($editor);
        $this->patchJson('/api/v1/app-state', [
            'site_maintenance_mode' => true,
        ])->assertForbidden();

        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);
        $this->patchJson('/api/v1/app-state', [
            'site_maintenance_mode' => true,
            'mobile_maintenance_mode' => true,
        ])
            ->assertOk()
            ->assertJsonPath('data.site_maintenance_mode', true)
            ->assertJsonPath('data.mobile_maintenance_mode', true);

        $this->assertDatabaseHas('site_settings', [
            'maintenance_mode' => true,
            'mobile_maintenance_mode' => true,
        ]);
        $this->assertDatabaseHas('system_logs', [
            'action' => 'toggle_mobile_maintenance_mode',
        ]);
    }

    public function test_active_announcement_is_returned_to_crm_user(): void
    {
        $user = User::factory()->create(['role' => 'content']);
        AppAnnouncement::query()->create([
            'title' => 'Важливе повідомлення',
            'body' => 'Текст для всіх працівників.',
            'expires_at' => now()->addDay(),
            'is_active' => true,
        ]);
        AppAnnouncement::query()->create([
            'title' => 'Застаріле',
            'body' => 'Не показувати.',
            'expires_at' => now()->subMinute(),
            'is_active' => true,
        ]);

        Sanctum::actingAs($user);
        $this->getJson('/api/v1/app-state')
            ->assertOk()
            ->assertJsonPath('data.announcement.title', 'Важливе повідомлення')
            ->assertJsonPath('data.can_manage', false);
    }

    public function test_admin_can_create_announcement_with_image_and_push_request(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        $response = $this->post('/api/v1/app-announcements', [
            'title' => 'Оголошення адміністратора',
            'body' => 'Перевірте нову інформацію.',
            'image' => UploadedFile::fake()->image('notice.jpg', 1000, 600),
            'link_url' => 'https://omc.pl.ua/news',
            'link_label' => 'Докладніше',
            'expires_at' => now()->addWeek()->toIso8601String(),
            'is_active' => '1',
            'send_push' => '1',
        ], ['Accept' => 'application/json']);

        $response
            ->assertCreated()
            ->assertJsonPath('data.title', 'Оголошення адміністратора')
            ->assertJsonPath('data.link_label', 'Докладніше');

        $announcement = AppAnnouncement::query()->firstOrFail();
        Storage::disk('public')->assertExists($announcement->image_path);
        $this->assertNotNull($announcement->push_requested_at);
        $this->assertDatabaseHas('system_logs', [
            'action' => 'create_app_announcement',
        ]);
    }

    public function test_editor_cannot_manage_announcements(): void
    {
        $editor = User::factory()->create(['role' => 'editor']);
        Sanctum::actingAs($editor);

        $this->getJson('/api/v1/app-announcements')->assertForbidden();
    }
}
