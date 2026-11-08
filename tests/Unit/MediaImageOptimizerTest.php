<?php

namespace Tests\Unit;

use App\Models\Department;
use App\Models\Employee;
use App\Models\MediaArchive;
use App\Models\Position;
use App\Support\MediaImageOptimizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class MediaImageOptimizerTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_photo_is_resized_converted_to_webp_and_replaces_the_source(): void
    {
        Storage::fake('public');
        $source = $this->writeJpeg('employees/portrait.jpg', 1200, 1600);
        $department = Department::query()->create(['name' => 'Адміністрація']);
        $position = Position::query()->create(['name' => 'Фахівець']);

        $employee = Employee::query()->create([
            'first_name' => 'Олена',
            'last_name' => 'Петренко',
            'department_id' => $department->id,
            'position_id' => $position->id,
            'photo' => $source,
        ])->refresh();

        $this->assertNotSame($source, $employee->photo);
        $this->assertStringEndsWith('.webp', $employee->photo);
        Storage::disk('public')->assertExists($source);
        Storage::disk('public')->assertExists($employee->photo);
        $this->assertDatabaseHas('media_archives', [
            'original_path' => $source,
            'reason' => 'optimized_original',
            'status' => MediaArchive::STATUS_PENDING,
        ]);
        [$width, $height, $type] = getimagesize(Storage::disk('public')->path($employee->photo));
        $this->assertSame(600, $width);
        $this->assertSame(800, $height);
        $this->assertSame(IMAGETYPE_WEBP, $type);
    }

    public function test_png_transparency_survives_webp_conversion(): void
    {
        Storage::fake('public');
        $path = 'employees/transparent.png';
        Storage::disk('public')->makeDirectory('employees');
        $image = imagecreatetruecolor(300, 300);
        imagealphablending($image, false);
        imagesavealpha($image, true);
        imagefill($image, 0, 0, imagecolorallocatealpha($image, 0, 0, 0, 127));
        imagefilledrectangle($image, 80, 80, 220, 220, imagecolorallocatealpha($image, 20, 40, 220, 0));
        imagepng($image, Storage::disk('public')->path($path));
        imagedestroy($image);

        $optimized = MediaImageOptimizer::optimize($path, [
            'max_width' => 150,
            'max_height' => 150,
            'quality' => 82,
        ]);

        $this->assertNotSame($path, $optimized);
        $converted = imagecreatefromwebp(Storage::disk('public')->path($optimized));
        $corner = imagecolorsforindex($converted, imagecolorat($converted, 0, 0));
        imagedestroy($converted);
        $this->assertGreaterThanOrEqual(120, $corner['alpha']);
    }

    public function test_non_image_files_are_left_untouched(): void
    {
        Storage::fake('public');
        $path = 'reports/report.pdf';
        Storage::disk('public')->put($path, '%PDF-1.7');

        $this->assertSame($path, MediaImageOptimizer::optimize($path));
        Storage::disk('public')->assertExists($path);
    }

    public function test_backfill_command_converts_existing_database_paths(): void
    {
        Storage::fake('public');
        $source = $this->writeJpeg('employees/legacy.jpg', 900, 1200);
        $department = Department::query()->create(['name' => 'Адміністрація']);
        $position = Position::query()->create(['name' => 'Фахівець']);
        $employeeId = DB::table('employees')->insertGetId([
            'first_name' => 'Ірина',
            'last_name' => 'Коваль',
            'department_id' => $department->id,
            'position_id' => $position->id,
            'photo' => $source,
            'sort' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->artisan('media:optimize-images', ['--model' => ['employees']])
            ->expectsOutput('employees: 1 converted')
            ->assertSuccessful();

        $optimized = Employee::query()->findOrFail($employeeId)->photo;
        $this->assertStringEndsWith('.webp', $optimized);
        Storage::disk('public')->assertExists($source);
        Storage::disk('public')->assertExists($optimized);
        $this->assertDatabaseHas('media_archives', [
            'original_path' => $source,
            'reason' => 'optimized_original',
        ]);
    }

    public function test_media_files_are_only_replaced_after_the_database_transaction_commits(): void
    {
        Storage::fake('public');
        $department = Department::query()->create(['name' => 'Адміністрація']);
        $position = Position::query()->create(['name' => 'Фахівець']);
        $employee = Employee::query()->create([
            'first_name' => 'Олена',
            'last_name' => 'Петренко',
            'department_id' => $department->id,
            'position_id' => $position->id,
            'photo' => $this->writeJpeg('employees/original.jpg', 900, 1200),
        ])->refresh();
        $original = $employee->photo;
        $rolledBack = $this->writeJpeg('employees/rollback.jpg', 900, 1200);

        try {
            DB::transaction(function () use ($employee, $rolledBack): void {
                $employee->update(['photo' => $rolledBack]);

                throw new RuntimeException('Rollback the media update.');
            });
        } catch (RuntimeException) {
            // Expected: neither optimization nor deletion may run on rollback.
        }

        $this->assertSame($original, $employee->refresh()->photo);
        Storage::disk('public')->assertExists([$original, $rolledBack]);

        $committed = $this->writeJpeg('employees/committed.jpg', 900, 1200);
        DB::transaction(fn () => $employee->update(['photo' => $committed]));
        $employee->refresh();

        $this->assertStringEndsWith('.webp', $employee->photo);
        $this->assertNotSame($committed, $employee->photo);
        Storage::disk('public')->assertExists($employee->photo);
        Storage::disk('public')->assertExists([$original, $committed]);
        $this->assertDatabaseHas('media_archives', ['original_path' => $original, 'reason' => 'replaced']);
        $this->assertDatabaseHas('media_archives', ['original_path' => $committed, 'reason' => 'optimized_original']);
    }

    private function writeJpeg(string $path, int $width, int $height): string
    {
        Storage::disk('public')->makeDirectory(dirname($path));
        $image = imagecreatetruecolor($width, $height);
        imagefill($image, 0, 0, imagecolorallocate($image, 40, 70, 220));
        imagejpeg($image, Storage::disk('public')->path($path), 92);
        imagedestroy($image);

        return $path;
    }
}
