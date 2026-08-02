<?php

namespace Tests\Feature\Api\V1;

use App\Enums\EventStatus;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Event;
use App\Models\HomepageSetting;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ContentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_homepage_settings_with_media(): void
    {
        Storage::fake('public');
        Sanctum::actingAs(User::factory()->create(['role' => 'admin']));

        $this->post('/api/v1/homepage-settings', [
            'contact_address' => 'м. Полтава',
            'contact_email' => 'info@example.com',
            'instagram_enabled' => '1',
            'instagram_url' => 'https://instagram.com/example',
            'logo_file' => UploadedFile::fake()->image('logo.png', 800, 800),
        ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('data.contact_address', 'м. Полтава')
            ->assertJsonPath('data.socials.instagram.enabled', true);

        $settings = HomepageSetting::query()->firstOrFail();
        Storage::disk('public')->assertExists($settings->logo);
        $this->assertDatabaseHas('system_logs', ['action' => 'update_homepage_settings']);
    }

    public function test_non_admin_can_read_but_cannot_update_homepage_settings(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'editor']));

        $this->getJson('/api/v1/homepage-settings')->assertOk();
        $this->postJson('/api/v1/homepage-settings', ['contact_address' => 'Зміна'])->assertForbidden();
    }

    public function test_admin_can_manage_team_and_editor_cannot(): void
    {
        Storage::fake('public');
        $position = Position::query()->create(['name' => 'Координатор']);
        $department = Department::query()->create(['name' => 'Програми']);
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        $response = $this->post('/api/v1/team', [
            'first_name' => 'Олена',
            'last_name' => 'Петренко',
            'position_id' => $position->id,
            'department_id' => $department->id,
            'photo_file' => UploadedFile::fake()->image('employee.jpg', 600, 800),
        ], ['Accept' => 'application/json']);

        $response->assertCreated()->assertJsonPath('data.full_name', 'Петренко Олена');
        $employee = Employee::query()->firstOrFail();
        Storage::disk('public')->assertExists($employee->photo);

        $this->getJson('/api/v1/team')->assertOk()->assertJsonPath('data.0.position.name', 'Координатор');

        Sanctum::actingAs(User::factory()->create(['role' => 'editor']));
        $this->deleteJson('/api/v1/team/'.$employee->id)->assertForbidden();
    }

    public function test_crm_user_can_create_summary_upload_gallery_and_delete_image(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['role' => 'content']);
        $event = Event::query()->create([
            'title' => 'Молодіжний форум',
            'description' => 'Опис',
            'event_date' => now()->addDay(),
            'status' => EventStatus::Published->value,
            'has_registration_button' => false,
            'registration_type' => 'none',
            'show_available_slots' => true,
            'user_id' => $user->id,
        ]);
        Sanctum::actingAs($user);

        $response = $this->post('/api/v1/events/'.$event->id.'/summary', [
            'summary' => 'Захід успішно відбувся.',
            'status' => 'published',
            'images' => [UploadedFile::fake()->image('gallery.jpg', 1200, 800)],
        ], ['Accept' => 'application/json']);

        $response
            ->assertOk()
            ->assertJsonPath('data.summary.status', 'published')
            ->assertJsonCount(1, 'data.summary.images');

        $summary = $event->summary()->with('images')->firstOrFail();
        Storage::disk('public')->assertExists($summary->images->first()->image);
        $this->assertDatabaseHas('event_summary_histories', ['action' => 'created', 'user_id' => $user->id]);

        $this->deleteJson('/api/v1/event-summary-images/'.$summary->images->first()->id)
            ->assertOk();
        $this->assertDatabaseCount('event_summary_images', 0);
    }
}
