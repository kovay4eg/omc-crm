<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventRegistrationController;



Route::get('/', function () {
    return view('welcome');
});
