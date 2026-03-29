<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventRegistrationController;

Route::post('/register', [EventRegistrationController::class, 'store']);