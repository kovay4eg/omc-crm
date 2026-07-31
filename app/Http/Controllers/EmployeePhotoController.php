<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Support\MediaStorage;

class EmployeePhotoController extends Controller
{
    public function show(Employee $employee)
    {
        abort_if(blank($employee->photo), 404);

        return MediaStorage::response($employee->photo);
    }
}
