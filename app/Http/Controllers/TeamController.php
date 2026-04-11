<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\SiteSetting;

class TeamController extends Controller
{
    public function index()
    {
        $departments = Department::with(['employees.position'])->get();
        $settings = SiteSetting::first();

        return view('team.index', compact('departments', 'settings'));
    }
}