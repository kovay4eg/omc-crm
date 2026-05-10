<?php

namespace App\Http\Controllers;

use App\Models\HomepageSetting;
use App\Models\Department;
use App\Models\SiteSetting;

class HomeController extends Controller
{
    public function index()
    {
        // Налаштування головної сторінки
        $settings = HomepageSetting::first();

        // Відділи + працівники
        $departments = Department::with('employees.position')->get();

        // Налаштування команди
        $siteSettings = SiteSetting::first();

        return view('index', [
            'settings' => $settings,
            'departments' => $departments,

            // лишаємо стару змінну
            'siteSettings' => $siteSettings,

            // додаємо settings для team section
            'teamSettings' => $siteSettings,
        ]);
    }
}