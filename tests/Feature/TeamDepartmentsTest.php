<?php

namespace Tests\Feature;

use App\Filament\Resources\Employees\Pages\EditEmployee;
use App\Filament\Resources\Employees\Pages\ListEmployees;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use App\Models\User;
use App\Services\TeamDepartmentService;
use Filament\Facades\Filament;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Tests\TestCase;

class TeamDepartmentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_form_saves_department_content_and_keeps_gallery_when_disabled(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $department = Department::create(['name' => 'Освіта']);
        $photo = UploadedFile::fake()->image('team.jpg')->store('team/departments', 'public');
        $slide = UploadedFile::fake()->image('slide.jpg')->store('team/departments', 'public');
        $secondSlide = UploadedFile::fake()->image('slide-2.jpg')->store('team/departments', 'public');

        Livewire::test(ListEmployees::class)
            ->callTableAction('manage_departments', data: ['departments' => [[
                'id' => $department->id, 'name' => 'Освіта', 'description' => 'Тренінги та навчання.',
                'photo' => [$photo], 'slider_enabled' => true, 'slider_photos' => [$slide, $secondSlide],
            ]]])
            ->assertHasNoTableActionErrors();

        $this->assertSame('Тренінги та навчання.', $department->fresh()->description);
        $this->assertTrue($department->fresh()->slider_enabled);
        $this->assertSame([$slide, $secondSlide], $department->fresh()->slider_photos);

        Livewire::test(ListEmployees::class)
            ->callTableAction('manage_departments', data: ['departments' => [[
                'id' => $department->id, 'name' => 'Освіта', 'description' => 'Тренінги та навчання.',
                'photo' => [$photo], 'slider_enabled' => true, 'slider_photos' => [$secondSlide, $slide],
            ]]])
            ->assertHasNoTableActionErrors();

        $this->assertSame([$secondSlide, $slide], $department->fresh()->slider_photos);

        Livewire::test(ListEmployees::class)
            ->callTableAction('manage_departments', data: ['departments' => [[
                'id' => $department->id, 'name' => 'Освіта', 'description' => 'Оновлений опис.',
                'photo' => [$photo], 'slider_enabled' => false, 'slider_photos' => [$secondSlide, $slide],
            ]]])
            ->assertHasNoTableActionErrors();

        $this->assertFalse($department->fresh()->slider_enabled);
        $this->assertSame([$secondSlide, $slide], $department->fresh()->slider_photos);
        Storage::disk('public')->assertExists([$photo, $slide, $secondSlide]);
    }

    public function test_admin_employee_form_saves_description_without_changing_other_fields(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $department = Department::create(['name' => 'Освіта']);
        $position = Position::create(['name' => 'Координатор']);
        $employee = Employee::create([
            'first_name' => 'Олена', 'last_name' => 'Петренко',
            'department_id' => $department->id, 'position_id' => $position->id,
        ]);

        Livewire::test(EditEmployee::class, ['record' => $employee->getRouteKey()])
            ->fillForm(['description' => 'Координує навчальні програми.'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Координує навчальні програми.', $employee->fresh()->description);
        $this->assertSame('Олена', $employee->fresh()->first_name);
        $this->assertSame($department->id, $employee->fresh()->department_id);
    }

    public function test_department_service_does_not_orphan_existing_employees_or_partially_save(): void
    {
        $keep = Department::create(['name' => 'Залишити', 'description' => 'Початковий опис']);
        $remove = Department::create(['name' => 'З працівником']);
        $position = Position::create(['name' => 'Координатор']);
        Employee::create(['first_name' => 'Ім’я', 'last_name' => 'Прізвище', 'department_id' => $remove->id, 'position_id' => $position->id]);
        try {
            app(TeamDepartmentService::class)->save([
                ['id' => $keep->id, 'name' => 'Залишити', 'description' => 'Неприпустима часткова зміна'],
            ], User::factory()->create(['role' => 'admin']));
            $this->fail('An occupied department must not be deleted.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('departments', $exception->errors());
        }
        $this->assertSame('Початковий опис', $keep->fresh()->description);
        $this->assertDatabaseHas('departments', ['id' => $remove->id]);
        $this->assertDatabaseHas('employees', ['department_id' => $remove->id]);
    }

    public function test_department_service_keeps_old_media_when_the_transaction_rolls_back(): void
    {
        Storage::fake('public');
        $oldPhoto = 'team/departments/old.jpg';
        $newPhoto = 'team/departments/new.jpg';
        Storage::disk('public')->put($oldPhoto, 'old');
        Storage::disk('public')->put($newPhoto, 'new');
        $first = Department::create(['name' => 'Перший', 'photo' => $oldPhoto]);
        $second = Department::create(['name' => 'Другий']);
        DB::statement('CREATE TRIGGER fail_second_department_update BEFORE UPDATE ON departments '
            .'WHEN OLD.id = '.(int) $second->id." BEGIN SELECT RAISE(ABORT, 'forced rollback'); END");

        try {
            app(TeamDepartmentService::class)->save([
                ['id' => $first->id, 'name' => 'Перший', 'photo' => $newPhoto],
                ['id' => $second->id, 'name' => 'Другий оновлений'],
            ], User::factory()->create(['role' => 'admin']));
            $this->fail('The forced database failure must roll the transaction back.');
        } catch (QueryException) {
            // Очікуваний збій другого UPDATE перевіряє атомарність БД і файлів.
        }

        $this->assertSame($oldPhoto, $first->fresh()->photo);
        $this->assertSame('Другий', $second->fresh()->name);
        Storage::disk('public')->assertExists($oldPhoto);
        Storage::disk('public')->assertMissing($newPhoto);
    }

    public function test_department_service_deletes_replaced_media_only_after_success(): void
    {
        Storage::fake('public');
        $oldPhoto = 'team/departments/old.jpg';
        $newPhoto = 'team/departments/new.jpg';
        Storage::disk('public')->put($oldPhoto, 'old');
        Storage::disk('public')->put($newPhoto, 'new');
        $department = Department::create(['name' => 'Освіта', 'photo' => $oldPhoto]);

        app(TeamDepartmentService::class)->save([
            ['id' => $department->id, 'name' => 'Освіта', 'photo' => $newPhoto],
        ], User::factory()->create(['role' => 'admin']));

        $this->assertSame($newPhoto, $department->fresh()->photo);
        Storage::disk('public')->assertMissing($oldPhoto);
        Storage::disk('public')->assertExists($newPhoto);
    }

    public function test_media_model_keeps_old_file_when_its_database_update_fails(): void
    {
        Storage::fake('public');
        $oldPhoto = 'team/departments/old.jpg';
        $newPhoto = 'team/departments/new.jpg';
        Storage::disk('public')->put($oldPhoto, 'old');
        Storage::disk('public')->put($newPhoto, 'new');
        $department = Department::create(['name' => 'Освіта', 'photo' => $oldPhoto]);
        DB::statement('CREATE TRIGGER fail_department_media_update BEFORE UPDATE ON departments '
            .'WHEN OLD.id = '.(int) $department->id." BEGIN SELECT RAISE(ABORT, 'forced update failure'); END");

        try {
            $department->update(['photo' => $newPhoto]);
            $this->fail('The forced database update failure must be reported.');
        } catch (QueryException) {
            // Подія updated не викликається після невдалого SQL-запиту.
        }

        $this->assertSame($oldPhoto, $department->fresh()->photo);
        Storage::disk('public')->assertExists($oldPhoto);
    }

    public function test_department_service_allows_editor_and_rejects_unsafe_media_paths(): void
    {
        $department = Department::create(['name' => 'Освіта']);
        app(TeamDepartmentService::class)->save([
            ['id' => $department->id, 'name' => 'Освітні програми'],
        ], User::factory()->create(['role' => 'editor']));
        $this->assertSame('Освітні програми', $department->fresh()->name);

        $this->expectException(ValidationException::class);
        app(TeamDepartmentService::class)->save([
            ['id' => $department->id, 'name' => 'Освітні програми', 'photo' => 'team/../../.env'],
        ], User::factory()->create(['role' => 'admin']));
    }

    public function test_department_media_and_pruning_preserve_current_and_disabled_gallery_files(): void
    {
        Storage::fake('public');
        Storage::fake('local');
        $photo = UploadedFile::fake()->image('department.jpg')->store('team/departments', 'public');
        $slide = UploadedFile::fake()->image('slide.jpg')->store('team/departments', 'public');
        $department = Department::create(['name' => 'Освіта', 'photo' => $photo, 'slider_photos' => [$slide]]);

        $this->get(route('departments.photo', $department))->assertOk();
        $this->get(route('departments.slide', [$department, 0]))->assertOk();
        $this->get(route('departments.photo', [$department, basename($photo)]))->assertOk();
        $this->get(route('departments.slide', [$department, 0, basename($slide)]))->assertOk();
        $this->get(route('departments.slide', [$department, 99]))->assertNotFound();
        $this->artisan('media:prune-orphans', ['--force' => true])->assertSuccessful();
        Storage::disk('public')->assertExists([$photo, $slide]);

        $department->update(['photo' => null, 'slider_photos' => [$photo, $slide]]);
        Storage::disk('public')->assertExists($photo);
        $department->delete();
        Storage::disk('public')->assertMissing([$photo, $slide]);
    }

    public function test_frontend_keeps_old_content_orders_employees_and_escapes_descriptions(): void
    {
        $department = Department::create([
            'name' => 'Освіта', 'description' => '<script>alert(1)</script>',
            'slider_enabled' => true, 'slider_photos' => ['team/slide.jpg'],
        ]);
        $position = Position::create(['name' => 'Координатор']);
        Employee::create(['first_name' => 'Другий', 'last_name' => 'Працівник', 'department_id' => $department->id, 'position_id' => $position->id, 'sort' => 2]);
        Employee::create(['first_name' => 'Перший', 'last_name' => 'Працівник', 'department_id' => $department->id, 'position_id' => $position->id, 'sort' => 1, 'description' => '<img src=x onerror=alert(1)>']);

        $this->get('/')
            ->assertOk()
            ->assertSeeInOrder([
                'id="about-section"',
                'id="team-section"',
                'id="toggleAboutMore"',
                'id="aboutMoreWrapper"',
                'id="statut-section"',
            ], false)
            ->assertSeeInOrder(['Працівник Перший', 'Працівник Другий'])
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertSee('&lt;img src=x onerror=alert(1)&gt;', false)
            ->assertSee('data-team-carousel', false)
            ->assertSee('data-department-section', false)
            ->assertSee('Анонси заходів');

        $department->update(['slider_enabled' => false]);
        $this->get('/')->assertOk()->assertDontSee('data-team-carousel', false);
        $department->update(['slider_enabled' => true, 'slider_photos' => []]);
        $this->get('/')->assertOk()->assertDontSee('data-team-carousel', false);
    }

    public function test_frontend_uses_administration_specific_sections_without_affecting_departments(): void
    {
        $department = Department::create([
            'name' => 'Адміністрація установи',
            'description' => 'Цей опис не повинен відображатися.',
            'slider_enabled' => true,
            'slider_photos' => ['team/departments/administration-slide.jpg'],
        ]);
        $position = Position::create(['name' => 'Керівниця']);
        Employee::create([
            'first_name' => 'Олена',
            'last_name' => 'Петренко',
            'department_id' => $department->id,
            'position_id' => $position->id,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('АДМІНІСТРАЦІЯ')
            ->assertSee('Петренко Олена')
            ->assertDontSee('Склад адміністрації')
            ->assertDontSee('Склад відділу')
            ->assertDontSee('Чим займається відділ?')
            ->assertDontSee('Цей опис не повинен відображатися.')
            ->assertDontSee('data-team-carousel', false)
            ->assertDontSee('data-department-section', false)
            ->assertDontSee('data-department-content', false);

        $department->update(['name' => 'Освіта']);

        $this->get('/')
            ->assertOk()
            ->assertSee('ВІДДІЛ ОСВІТА')
            ->assertSee('Чим займається відділ?')
            ->assertSee('Склад відділу')
            ->assertSee('Цей опис не повинен відображатися.')
            ->assertSee('data-team-carousel', false);
    }

    public function test_admin_department_form_hides_irrelevant_administration_fields_without_erasing_data(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $department = Department::create([
            'name' => 'Адміністрація',
            'description' => 'Збережений опис.',
            'slider_enabled' => true,
            'slider_photos' => ['team/departments/administration-slide.jpg'],
        ]);

        Livewire::test(ListEmployees::class)
            ->mountTableAction('manage_departments')
            ->assertDontSee('Чим займається відділ?')
            ->assertDontSee('Увімкнути фотослайдер');

        Livewire::test(ListEmployees::class)
            ->callTableAction('manage_departments', data: ['departments' => [[
                'id' => $department->id,
                'name' => 'Адміністрація',
            ]]])
            ->assertHasNoTableActionErrors();

        $department->refresh();
        $this->assertSame('Збережений опис.', $department->description);
        $this->assertTrue($department->slider_enabled);
        $this->assertSame(['team/departments/administration-slide.jpg'], $department->slider_photos);
    }

    public function test_existing_mobile_update_preserves_the_new_employee_description(): void
    {
        $department = Department::create([
            'name' => 'Освіта', 'description' => 'Збережений опис відділу',
            'photo' => 'team/departments/cover.jpg', 'slider_enabled' => true,
            'slider_photos' => ['team/departments/slide.jpg'],
        ]);
        $position = Position::create(['name' => 'Координатор']);
        $employee = Employee::create(['first_name' => 'Олена', 'last_name' => 'Петренко', 'department_id' => $department->id, 'position_id' => $position->id, 'description' => 'Збережений опис']);
        Sanctum::actingAs(User::factory()->create(['role' => 'admin']));
        $this->postJson('/api/v1/team/'.$employee->id, [
            'first_name' => 'Олена', 'last_name' => 'Петренко', 'department_id' => $department->id, 'position_id' => $position->id,
        ])->assertOk();
        $this->assertSame('Збережений опис', $employee->fresh()->description);
        $this->assertSame('Збережений опис відділу', $department->fresh()->description);
        $this->assertSame('team/departments/cover.jpg', $department->fresh()->photo);
        $this->assertSame(['team/departments/slide.jpg'], $department->fresh()->slider_photos);
        $this->assertTrue($department->fresh()->slider_enabled);
        $this->getJson('/api/v1/team/options')
            ->assertOk()
            ->assertJsonPath('data.departments.0.id', $department->id)
            ->assertJsonPath('data.departments.0.name', 'Освіта')
            ->assertJsonStructure(['data' => ['positions', 'departments', 'team_banner_url']]);
        $this->getJson('/api/v1/team')
            ->assertOk()
            ->assertJsonPath('data.0.first_name', 'Олена')
            ->assertJsonStructure(['data' => [['id', 'first_name', 'last_name', 'middle_name', 'full_name', 'photo_url', 'position' => ['id', 'name'], 'department' => ['id', 'name']]]]);
    }
}
