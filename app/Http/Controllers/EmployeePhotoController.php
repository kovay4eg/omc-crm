<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Support\Facades\Storage;

class EmployeePhotoController extends Controller
{
    public function show(Employee $employee)
    {
        $photoPath = ltrim((string) $employee->photo, '/');

        abort_if($photoPath === '', 404);

        foreach (['public', 'local'] as $disk) {
            if (!Storage::disk($disk)->exists($photoPath)) {
                continue;
            }

            return response()->file(Storage::disk($disk)->path($photoPath), [
                'Cache-Control' => 'public, max-age=86400',
            ]);
        }

        abort(404);
    }
}
