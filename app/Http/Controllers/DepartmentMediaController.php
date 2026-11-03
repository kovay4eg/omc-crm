<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Support\MediaStorage;

class DepartmentMediaController extends Controller
{
    public function photo(Department $department)
    {
        abort_if(blank($department->photo), 404);

        return MediaStorage::response($department->photo);
    }

    public function slide(Department $department, int $index)
    {
        $photos = array_values($department->slider_photos ?? []);
        abort_unless(isset($photos[$index]) && is_string($photos[$index]), 404);

        return MediaStorage::response($photos[$index]);
    }
}
