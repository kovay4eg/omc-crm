<?php

namespace Tests\Feature;

use App\Filament\Resources\Employees\Pages\EditEmployee;
use App\Filament\Resources\Employees\Pages\ListEmployees;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\TeamDepartmentService;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TeamValidationTest extends TestCase
{
    use RefreshDatabase;

    private function administrator(): User
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        return $admin;
    }

    public function test_admin_can_upload_new_department_cover_and_gallery(): void
    {
        Storage::fake('public');
        $this->administrator();
        $department = Department::create(['name' => 'Освіта']);
        Livewire::test(ListEmployees::class)
            ->callTableAction('manage_departments', data: ['departments' => [[
                'id' => $department->id, 'name' => 'Освіта', 'slider_enabled' => true,
                'photo' => [UploadedFile::fake()->image('cover.jpg')],
                'slider_photos' => [UploadedFile::fake()->image('slide.png')],
            ]]])
            ->assertHasNoTableActionErrors();
        $department->refresh();
        $this->assertNotNull($department->photo);
        $this->assertCount(1, $department->slider_photos);
        Storage::disk('public')->assertExists([$department->photo, ...$department->slider_photos]);
    }

    public function test_occupied_department_error_is_visible_in_admin_form(): void
    {
        $this->administrator();
        $department = Department::create(['name' => 'Освіта']);
        $position = Position::create(['name' => 'Координатор']);
        Employee::create(['first_name' => 'Олена', 'last_name' => 'Петренко', 'department_id' => $department->id, 'position_id' => $position->id]);
        Livewire::test(ListEmployees::class)
            ->callTableAction('manage_departments', data: ['departments' => []])
            ->assertHasTableActionErrors(['departments']);
        $this->assertDatabaseHas('departments', ['id' => $department->id]);
    }

    public function test_employee_description_limit_blocks_partial_changes(): void
    {
        $this->administrator();
        $department = Department::create(['name' => 'Освіта']);
        $position = Position::create(['name' => 'Координатор']);
        $employee = Employee::create(['first_name' => 'Олена', 'last_name' => 'Петренко', 'department_id' => $department->id, 'position_id' => $position->id, 'description' => 'Початковий опис']);
        Livewire::test(EditEmployee::class, ['record' => $employee->getRouteKey()])
            ->fillForm(['first_name' => 'Змінене', 'description' => str_repeat('а', 5001)])
            ->call('save')
            ->assertHasFormErrors(['description' => 'max']);
        $this->assertSame('Олена', $employee->fresh()->first_name);
        $this->assertSame('Початковий опис', $employee->fresh()->description);
    }

    public function test_employee_photo_editor_round_trip_and_legacy_photo_routes(): void
    {
        Storage::fake('public');
        Storage::fake('local');
        $this->administrator();
        $department = Department::create(['name' => 'Освіта']);
        $position = Position::create(['name' => 'Координатор']);
        $oldPhoto = UploadedFile::fake()->image('old.jpg')->store('employees', 'local');
        $employee = Employee::create(['first_name' => 'Олена', 'last_name' => 'Петренко', 'department_id' => $department->id, 'position_id' => $position->id, 'photo' => $oldPhoto]);
        $this->get(route('employees.photo', $employee))->assertOk();
        $this->get(route('employees.photo', [$employee, basename($oldPhoto)]))->assertOk();
        Livewire::test(EditEmployee::class, ['record' => $employee->getRouteKey()])
            ->fillForm(['photo' => [UploadedFile::fake()->image('edited.png', 300, 400)]])
            ->call('save')
            ->assertHasNoFormErrors();
        $employee->refresh();
        Storage::disk('public')->assertExists($employee->photo);
        Storage::disk('local')->assertMissing($oldPhoto);
        $this->get(route('employees.photo', [$employee, basename($employee->photo)]))->assertOk();
    }

    public function test_overall_team_photo_upload_works_with_existing_banner_action(): void
    {
        Storage::fake('public');
        $this->administrator();
        Livewire::test(ListEmployees::class)
            ->callTableAction('edit_team_banner', data: ['team_banner' => [UploadedFile::fake()->image('banner.jpg', 210, 90)]])
            ->assertHasNoTableActionErrors();
        $photo = SiteSetting::firstOrFail()->team_banner;
        $this->assertNotNull($photo);
        Storage::disk('public')->assertExists($photo);
    }

    public function test_twenty_one_slides_are_rejected_in_the_actual_admin_form(): void
    {
        Storage::fake('public');
        $this->administrator();
        $department = Department::create(['name' => 'Освіта']);
        $photos = collect(range(1, 21))->map(fn ($index) => UploadedFile::fake()->image('slide-'.$index.'.jpg')->store('team/departments', 'public'))->all();
        Livewire::test(ListEmployees::class)
            ->callTableAction('manage_departments', data: ['departments' => [[
                'id' => $department->id, 'name' => 'Освіта', 'slider_enabled' => true, 'slider_photos' => $photos,
            ]]])
            ->assertHasTableActionErrors(['departments.0.slider_photos']);
        $this->assertNull($department->fresh()->slider_photos);
    }

    public function test_editor_cannot_open_department_management_action(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'editor']));
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Livewire::test(ListEmployees::class)->assertTableActionHidden('manage_departments');
    }

    #[DataProvider('invalidPhotoUpload')]
    public function test_employee_and_banner_photo_limits_are_enforced(string $kind): void
    {
        Storage::fake('public');
        $this->administrator();
        $department = Department::create(['name' => 'Освіта']);
        $position = Position::create(['name' => 'Координатор']);
        $employee = Employee::create(['first_name' => 'Олена', 'last_name' => 'Петренко', 'department_id' => $department->id, 'position_id' => $position->id]);
        $invalid = fn () => match ($kind) {
            'pdf' => UploadedFile::fake()->create('photo.pdf', 10, 'application/pdf'),
            'svg' => UploadedFile::fake()->create('photo.svg', 10, 'image/svg+xml'),
            default => UploadedFile::fake()->image('photo.jpg')->size(10241),
        };
        Livewire::test(EditEmployee::class, ['record' => $employee->getRouteKey()])
            ->fillForm(['photo' => [$invalid()]])
            ->call('save')
            ->assertHasFormErrors(['photo']);
        Livewire::test(ListEmployees::class)
            ->callTableAction('edit_team_banner', data: ['team_banner' => [$invalid()]])
            ->assertHasTableActionErrors(['team_banner']);
        $this->assertNull($employee->fresh()->photo);
    }

    #[DataProvider('invalidDepartmentContent')]
    public function test_invalid_department_data_does_not_modify_existing_content(array $invalid): void
    {
        $admin = $this->administrator();
        $department = Department::create(['name' => 'Освіта', 'description' => 'Початковий опис']);
        try {
            app(TeamDepartmentService::class)->save([
                array_replace(['id' => $department->id, 'name' => 'Освіта', 'description' => 'Змінений опис'], $invalid),
            ], $admin);
            $this->fail('Invalid department data must be rejected.');
        } catch (ValidationException $exception) {
            $this->assertNotEmpty($exception->errors());
        }
        $this->assertSame('Початковий опис', $department->fresh()->description);
    }

    public static function invalidDepartmentContent(): array
    {
        return [
            'empty name' => [['name' => '   ']],
            'prefix only' => [['name' => 'відділ ']],
            'long name' => [['name' => str_repeat('а', 256)]],
            'long description' => [['description' => str_repeat('а', 10001)]],
            'invalid toggle' => [['slider_enabled' => 'yes']],
            'invalid gallery' => [['slider_photos' => 'photo.jpg']],
            'too many slides' => [['slider_photos' => array_fill(0, 21, 'team/departments/photo.jpg')]],
            'unsafe path' => [['photo' => 'team/../secret.jpg']],
            'foreign path' => [['photo' => 'events/photo.jpg']],
            'other team media' => [['photo' => 'team/banner.jpg']],
        ];
    }

    #[DataProvider('invalidPhotoUpload')]
    public function test_department_photo_upload_rejects_invalid_type_and_size(string $kind): void
    {
        Storage::fake('public');
        $this->administrator();
        $department = Department::create(['name' => 'Освіта']);
        $file = match ($kind) {
            'pdf' => UploadedFile::fake()->create('photo.pdf', 10, 'application/pdf'),
            'svg' => UploadedFile::fake()->create('photo.svg', 10, 'image/svg+xml'),
            default => UploadedFile::fake()->image('photo.jpg')->size(10241),
        };
        Livewire::test(ListEmployees::class)
            ->callTableAction('manage_departments', data: ['departments' => [[
                'id' => $department->id, 'name' => 'Освіта', 'photo' => [$file], 'slider_enabled' => false,
            ]]])
            ->assertHasTableActionErrors();
        $this->assertNull($department->fresh()->photo);
    }

    public static function invalidPhotoUpload(): array
    {
        return [['pdf'], ['svg'], ['too large']];
    }
}
