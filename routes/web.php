<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventRegistrationController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CalendarPlanController;

use App\Models\Report;
use App\Models\CalendarPlan;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index']);

Route::post('/exit-preview', function () {
    session()->forget('preview_role');
    return back();
})->name('exit-preview');

Route::get('/login', function () {
    return redirect('/admin/login');
})->name('login');

Route::get('/reporting', function () {

    $reports = Report::orderBy('year', 'desc')->get();

    return view('reporting.reporting', compact('reports'));

})->name('reporting');

Route::get('/calendar-plan', function () {

    $calendarPlans = CalendarPlan::orderBy('year', 'desc')->get();

    return view('calendar_plan.calendar_plan', compact('calendarPlans'));

})->name('calendar-plan');

// GOOGLE
Route::get('/google/redirect', [GoogleController::class, 'redirect'])->name('google.connect');

Route::get('/google/callback', [GoogleController::class, 'callback']);