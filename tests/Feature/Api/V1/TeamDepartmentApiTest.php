<?php

namespace Tests\Feature\Api\V1;

use App\Http\Middleware\EnsureFrontendIsAvailable;
use App\Http\Middleware\TrackSiteVisit;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use App\Models\User;
use App\Support\TeamDepartmentContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TeamDepartmentApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Sanctum::actingAs(User::factory()->create(['role' => 'admin']));
    }

    private function photo(string $name): string
    {
        return UploadedFile::fake()->image($name)->store('team/departments', 'public');
    }

    private function updateData(Department $department, array $data = []): array
    {
        return array_merge(['name' => $department->name, 'revision' => TeamDepartmentContent::revision($department->fresh())], $data);
    }

    public function test_options_include_editable_content_without_breaking_legacy_fields(): void
    {
        $photo = $this->photo('cover.jpg');
        $slide = $this->photo('slide.jpg');
        $department = Department::create(['name' => 'Освіта', 'description' => 'Навчання', 'photo' => $photo, 'slider_enabled' => true, 'slider_photos' => [$slide]]);
        $response = $this->getJson('/api/v1/team/options')->assertOk()
            ->assertJsonPath('data.department_content_supported', true)
            ->assertJsonPath('data.departments.0.id', $department->id)
            ->assertJsonPath('data.departments.0.name', 'Освіта')
            ->assertJsonPath('data.departments.0.is_administration', false)
            ->assertJsonPath('data.departments.0.description', 'Навчання')
            ->assertJsonPath('data.departments.0.slider_enabled', true)
            ->assertJsonPath('data.departments.0.slider_photos.0.id', TeamDepartmentContent::photoId($slide));
        $this->assertStringContainsString('/photo/'.basename($photo).'?v=', $response->json('data.departments.0.photo_url'));
        $this->assertStringContainsString('/slides/0/'.basename($slide).'?v=', $response->json('data.departments.0.slider_photos.0.url'));
        $this->assertArrayNotHasKey('photo', $response->json('data.departments.0'));
        $this->assertSame(64, strlen($response->json('data.departments.0.revision')));
    }

    public function test_options_identify_administration_for_mobile_presentation(): void
    {
        $department = Department::create(['name' => 'Адміністрація установи']);

        $this->getJson('/api/v1/team/options')
            ->assertOk()
            ->assertJsonPath('data.departments.0.id', $department->id)
            ->assertJsonPath('data.departments.0.is_administration', true);
    }

    public function test_admin_creates_department_with_cover_and_ordered_gallery_uploads(): void
    {
        $response = $this->post('/api/v1/team/departments', [
            'name' => 'Відділ Освіта', 'description' => 'Навчання', 'slider_enabled' => '1',
            'photo_file' => UploadedFile::fake()->image('cover.png'),
            'slider_files' => [UploadedFile::fake()->image('first.png'), UploadedFile::fake()->image('second.png')],
            'gallery_order' => json_encode(['upload:1', 'upload:0']),
        ], ['Accept' => 'application/json'])->assertCreated();
        $department = Department::findOrFail($response->json('data.id'));
        $this->assertSame('Освіта', $department->name);
        $this->assertTrue($department->slider_enabled);
        $this->assertCount(2, $department->slider_photos);
        Storage::disk('public')->assertExists([$department->photo, ...$department->slider_photos]);
        $this->getJson('/api/v1/team/options')->assertJsonPath('data.departments.0.slider_photos.0.id', TeamDepartmentContent::photoId($department->slider_photos[0]));
    }

    public function test_reorder_replace_remove_and_disable_keep_the_intended_files(): void
    {
        $cover = $this->photo('cover.jpg');
        $first = $this->photo('first.jpg');
        $second = $this->photo('second.jpg');
        $department = Department::create(['name' => 'Освіта', 'photo' => $cover, 'slider_enabled' => true, 'slider_photos' => [$first, $second]]);
        $this->postJson('/api/v1/team/departments/'.$department->id, $this->updateData($department, [
            'gallery_order' => json_encode(['existing:'.TeamDepartmentContent::photoId($second), 'existing:'.TeamDepartmentContent::photoId($first)]),
        ]))->assertOk();
        $this->assertSame([$second, $first], $department->fresh()->slider_photos);
        $this->post('/api/v1/team/departments/'.$department->id, $this->updateData($department, [
            'slider_enabled' => '0', 'remove_photo' => '1',
            'slider_files' => [UploadedFile::fake()->image('edited.png')],
            'gallery_order' => json_encode(['upload:0', 'existing:'.TeamDepartmentContent::photoId($first)]),
        ]), ['Accept' => 'application/json'])->assertOk();
        $department->refresh();
        $this->assertNull($department->photo);
        $this->assertFalse($department->slider_enabled);
        $this->assertSame($first, $department->slider_photos[1]);
        Storage::disk('public')->assertExists([$first, $department->slider_photos[0]]);
        Storage::disk('public')->assertMissing([$cover, $second]);
        $this->get('/')->assertOk()->assertDontSee('data-team-carousel', false);
    }

    public function test_omitting_media_fields_preserves_existing_media_and_content(): void
    {
        $cover = $this->photo('cover.jpg');
        $slide = $this->photo('slide.jpg');
        $department = Department::create(['name' => 'Освіта', 'description' => 'Збережений опис', 'photo' => $cover, 'slider_enabled' => true, 'slider_photos' => [$slide]]);
        $this->postJson('/api/v1/team/departments/'.$department->id, $this->updateData($department, ['name' => 'Оновлена назва']))->assertOk();
        $department->refresh();
        $this->assertSame('Збережений опис', $department->description);
        $this->assertSame($cover, $department->photo);
        $this->assertSame([$slide], $department->slider_photos);
        $this->assertTrue($department->slider_enabled);
        Storage::disk('public')->assertExists([$cover, $slide]);
    }

    public function test_stale_editor_cannot_overwrite_web_edits_or_delete_department(): void
    {
        $department = Department::create(['name' => 'Освіта', 'description' => 'Початковий']);
        $stale = $this->updateData($department, ['description' => 'Застаріла чернетка']);
        $department->update(['description' => 'Оновлено через адмінку']);
        $this->postJson('/api/v1/team/departments/'.$department->id, $stale)->assertConflict();
        $this->deleteJson('/api/v1/team/departments/'.$department->id, ['revision' => $stale['revision']])->assertConflict();
        $this->assertSame('Оновлено через адмінку', $department->fresh()->description);
    }

    public function test_gallery_tokens_cannot_reference_another_department_or_repeat_files(): void
    {
        $first = $this->photo('first.jpg');
        $foreign = $this->photo('foreign.jpg');
        $department = Department::create(['name' => 'Освіта', 'slider_photos' => [$first]]);
        Department::create(['name' => 'Інший', 'slider_photos' => [$foreign]]);
        foreach ([['existing:'.TeamDepartmentContent::photoId($foreign)], ['existing:'.TeamDepartmentContent::photoId($first), 'existing:'.TeamDepartmentContent::photoId($first)], ['upload:0'], ['team/../../.env']] as $order) {
            $this->postJson('/api/v1/team/departments/'.$department->id, $this->updateData($department, ['gallery_order' => json_encode($order)]))->assertUnprocessable()->assertJsonValidationErrors('gallery_order');
        }
        $this->assertSame([$first], $department->fresh()->slider_photos);
        Storage::disk('public')->assertExists([$first, $foreign]);
    }

    public function test_gallery_rejects_oversized_list_and_unreferenced_upload_without_creating_orphans(): void
    {
        $before = Storage::disk('public')->allFiles();
        $this->post('/api/v1/team/departments', [
            'name' => 'Освіта', 'slider_files' => [UploadedFile::fake()->image('unused.jpg')], 'gallery_order' => '[]',
        ], ['Accept' => 'application/json'])->assertUnprocessable()->assertJsonValidationErrors('gallery_order');
        $this->postJson('/api/v1/team/departments', ['name' => 'Освіта', 'gallery_order' => json_encode(array_fill(0, 21, 'upload:0'))])->assertUnprocessable();
        $this->assertSame($before, Storage::disk('public')->allFiles());
        $this->assertDatabaseCount('departments', 0);
    }

    public static function invalidUploads(): array
    {
        return [
            'pdf cover' => ['photo_file', 'document.pdf', 'application/pdf', 10],
            'svg gallery' => ['slider_files', 'script.svg', 'image/svg+xml', 10],
            'large cover' => ['photo_file', 'photo.jpg', 'image/jpeg', 10241],
        ];
    }

    #[DataProvider('invalidUploads')]
    public function test_invalid_media_is_rejected(string $field, string $name, string $mime, int $size): void
    {
        $file = UploadedFile::fake()->create($name, $size, $mime);
        $this->post('/api/v1/team/departments', [
            'name' => 'Освіта', $field => $field === 'slider_files' ? [$file] : $file,
        ], ['Accept' => 'application/json'])->assertUnprocessable();
        $this->assertDatabaseCount('departments', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_empty_department_name_and_long_descriptions_are_rejected(): void
    {
        $this->postJson('/api/v1/team/departments', ['name' => 'Відділ '])->assertUnprocessable()->assertJsonValidationErrors('name');
        $this->postJson('/api/v1/team/departments', ['name' => 'Освіта', 'description' => str_repeat('x', 10001)])->assertUnprocessable()->assertJsonValidationErrors('description');
    }

    public function test_editor_can_manage_departments_but_content_user_cannot(): void
    {
        $department = Department::create(['name' => 'Освіта']);
        Sanctum::actingAs(User::factory()->create(['role' => 'editor']));
        $this->postJson('/api/v1/team/departments/'.$department->id, $this->updateData($department, [
            'description' => 'Оновлено редактором.',
        ]))->assertOk();
        $this->assertSame('Оновлено редактором.', $department->fresh()->description);

        Sanctum::actingAs(User::factory()->create(['role' => 'content']));
        $this->getJson('/api/v1/team/options')->assertOk();
        $this->postJson('/api/v1/team/departments', ['name' => 'Інший'])->assertForbidden();
        $this->postJson('/api/v1/team/departments/'.$department->id, $this->updateData($department))->assertForbidden();
        $this->deleteJson('/api/v1/team/departments/'.$department->id, [
            'revision' => TeamDepartmentContent::revision($department),
        ])->assertForbidden();
    }

    public function test_occupied_department_is_protected_and_empty_department_media_is_cleaned_up(): void
    {
        $photo = $this->photo('cover.jpg');
        $department = Department::create(['name' => 'Освіта', 'photo' => $photo]);
        $position = Position::create(['name' => 'Координатор']);
        $employee = Employee::create(['first_name' => 'Олена', 'last_name' => 'Петренко', 'position_id' => $position->id, 'department_id' => $department->id]);
        $this->deleteJson('/api/v1/team/departments/'.$department->id, ['revision' => TeamDepartmentContent::revision($department)])->assertUnprocessable();
        Storage::disk('public')->assertExists($photo);
        $employee->update(['department_id' => null]);
        $this->deleteJson('/api/v1/team/departments/'.$department->id, ['revision' => TeamDepartmentContent::revision($department)])->assertOk();
        $this->assertDatabaseMissing('departments', ['id' => $department->id]);
        Storage::disk('public')->assertMissing($photo);
    }

    public function test_team_media_stays_available_to_crm_during_frontend_maintenance(): void
    {
        foreach (['employees.photo', 'departments.photo', 'departments.slide'] as $name) {
            $middleware = app('router')->getRoutes()->getByName($name)->gatherMiddleware();
            $this->assertNotContains(EnsureFrontendIsAvailable::class, $middleware);
            $this->assertNotContains(TrackSiteVisit::class, $middleware);
        }
        $this->assertContains(EnsureFrontendIsAvailable::class, app('router')->getRoutes()->match(Request::create('/'))->gatherMiddleware());
    }

    public function test_employee_description_round_trip_and_legacy_update_preservation(): void
    {
        $position = Position::create(['name' => 'Координатор']);
        $fields = ['first_name' => 'Олена', 'last_name' => 'Петренко', 'position_id' => $position->id];
        $response = $this->postJson('/api/v1/team', $fields + ['description' => 'Координує роботу'])->assertCreated()->assertJsonPath('data.description', 'Координує роботу');
        $id = $response->json('data.id');
        $this->postJson('/api/v1/team/'.$id, $fields)->assertOk()->assertJsonPath('data.description', 'Координує роботу');
        $this->postJson('/api/v1/team/'.$id, $fields + ['description' => str_repeat('x', 5001)])->assertUnprocessable()->assertJsonValidationErrors('description');
        $this->postJson('/api/v1/team/'.$id, $fields + ['description' => ''])->assertOk()->assertJsonPath('data.description', null);
    }
}
