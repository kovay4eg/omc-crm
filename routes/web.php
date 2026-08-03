<?php

use App\Http\Controllers\EmployeePhotoController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventImageController;
use App\Http\Controllers\EventShareImageController;
use App\Http\Controllers\EventSummaryController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\HomeController;
use App\Http\Middleware\EnsureFrontendIsAvailable;
use App\Http\Middleware\TrackSiteVisit;
use App\Models\CalendarPlan;
use App\Models\Report;
use Illuminate\Support\Facades\Route;

Route::middleware([EnsureFrontendIsAvailable::class, TrackSiteVisit::class])->group(function () {
    Route::get('/', [HomeController::class, 'index']);

    Route::get('/team/employees/{employee}/photo', [EmployeePhotoController::class, 'show'])
        ->name('employees.photo');

    Route::get('/events/{event}/image', [EventImageController::class, 'show'])
        ->name('events.image');

    Route::get('/events/{event}/smm-image', [EventImageController::class, 'showSmmImage'])
        ->name('events.smm-image');

    Route::get('/events/{event}/share-image.jpg', [EventShareImageController::class, 'show'])
        ->name('events.share-image');

    Route::get('/events/{event}', [EventController::class, 'show'])
        ->name('events.show');

    Route::get('/event-summaries/{eventSummary}', [EventSummaryController::class, 'show'])
        ->name('event-summaries.show');

    Route::get('/reporting', function () {
        $reports = Report::orderBy('year', 'desc')->get();

        return view('reporting.reporting', compact('reports'));
    })->name('reporting');

    Route::get('/calendar-plan', function () {
        $calendarPlans = CalendarPlan::orderBy('year', 'desc')->get();

        return view('calendar_plan.calendar_plan', compact('calendarPlans'));
    })->name('calendar-plan');
});

Route::middleware('auth')->get('/admin/event-summaries/{event}/preview', [EventSummaryController::class, 'preview'])
    ->name('event-summaries.preview');

Route::post('/exit-preview', function () {
    session()->forget('preview_role');

    return back();
})->name('exit-preview');

Route::get('/login', function () {
    return redirect('/admin/login');
})->name('login');

Route::get('/google/redirect', [GoogleController::class, 'redirect'])->name('google.connect');

Route::get('/google/callback', [GoogleController::class, 'callback']);
