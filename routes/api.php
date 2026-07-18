<?php

use App\Http\Controllers\EventRegistrationController;
use App\Http\Controllers\MaintenanceGameScoreController;
use App\Http\Middleware\EnsureFrontendIsAvailable;
use Illuminate\Support\Facades\Route;

Route::post('/register', [EventRegistrationController::class, 'store'])
    ->middleware(EnsureFrontendIsAvailable::class);

Route::get('/maintenance-game/leaderboard', [MaintenanceGameScoreController::class, 'index'])
    ->middleware('throttle:30,1');

Route::post('/maintenance-game/scores', [MaintenanceGameScoreController::class, 'store'])
    ->middleware('throttle:8,1');
