<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventRegistrationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});


// 🔥 ВИХІД З РЕЖИМУ ПЕРЕГЛЯДУ
Route::post('/exit-preview', function () {
    session()->forget('preview_role');
    return back();
})->name('exit-preview');