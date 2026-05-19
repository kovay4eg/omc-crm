<?php

namespace App\Http\Controllers;

use App\Models\HomepageSetting;
use App\Models\Department;
use App\Models\SiteSetting;
use App\Models\Report;
use App\Models\CalendarPlan;

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

        // Звітність
        $reports = Report::orderBy('year', 'desc')->get();

        // Календарні плани
        $calendarPlans = CalendarPlan::orderBy('year', 'desc')->get();

        return view('index', [
            'settings' => $settings,
            'departments' => $departments,

            // лишаємо стару змінну
            'siteSettings' => $siteSettings,

            // додаємо settings для team section
            'teamSettings' => $siteSettings,

            // звітність
            'reports' => $reports,

            // календарний план
            'calendarPlans' => $calendarPlans,
        ]);
    }
}