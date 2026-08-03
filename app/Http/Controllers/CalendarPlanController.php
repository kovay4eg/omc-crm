<?php

namespace App\Http\Controllers;

use App\Models\CalendarPlan;

class CalendarPlanController extends Controller
{
    public function index()
    {
        $calendarPlans = CalendarPlan::orderBy('year', 'desc')->get();

        return view('calendar_plan.calendar_plan', compact('calendarPlans'));
    }
}
