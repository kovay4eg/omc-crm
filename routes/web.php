<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventRegistrationController;
use App\Http\Controllers\GoogleController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

Route::post('/exit-preview', function () {
    session()->forget('preview_role');
    return back();
})->name('exit-preview');

Route::get('/login', function () {
    return redirect('/admin/login');
})->name('login');


// GOOGLE
Route::get('/google/redirect', [GoogleController::class, 'redirect'])->name('google.connect');
Route::get('/google/callback', [GoogleController::class, 'callback']);