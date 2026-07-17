<?php

use App\Http\Controllers\EventRegistrationController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [EventRegistrationController::class, 'store']);