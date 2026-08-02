<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\AppAnnouncementController as ApiAppAnnouncementController;
use App\Http\Controllers\Api\V1\AppStateController as ApiAppStateController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\EventController as ApiEventController;
use App\Http\Controllers\Api\V1\EventRegistrationController as ApiEventRegistrationController;
use App\Http\Controllers\Api\V1\SystemLogController as ApiSystemLogController;
use App\Http\Controllers\Api\V1\TwoFactorController as ApiTwoFactorController;
use App\Http\Controllers\Api\V1\UserController as ApiUserController;
use App\Http\Controllers\EventRegistrationController;
use App\Http\Controllers\MaintenanceGameScoreController;
use App\Http\Middleware\EnsureCrmApiAccess;
use App\Http\Middleware\EnsureFrontendIsAvailable;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1');

    Route::middleware(['auth:sanctum', EnsureCrmApiAccess::class])->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::get('/dashboard', DashboardController::class);
        Route::get('/app-state', [ApiAppStateController::class, 'show']);
        Route::patch('/app-state', [ApiAppStateController::class, 'update']);
        Route::apiResource('app-announcements', ApiAppAnnouncementController::class)
            ->except('show');
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/sessions', [AuthController::class, 'sessions']);
        Route::delete('/auth/sessions/others', [AuthController::class, 'destroyOtherSessions']);
        Route::delete('/auth/sessions/{token}', [AuthController::class, 'destroySession'])
            ->whereNumber('token');
        Route::get('/auth/two-factor', [ApiTwoFactorController::class, 'status']);
        Route::post('/auth/two-factor/prepare', [ApiTwoFactorController::class, 'prepare']);
        Route::post('/auth/two-factor/confirm', [ApiTwoFactorController::class, 'confirm']);
        Route::post('/auth/two-factor/recovery-codes', [ApiTwoFactorController::class, 'regenerate']);
        Route::delete('/auth/two-factor', [ApiTwoFactorController::class, 'disable']);

        Route::get('/events', [ApiEventController::class, 'index']);
        Route::post('/events', [ApiEventController::class, 'store']);
        Route::get('/events/{event}', [ApiEventController::class, 'show']);
        Route::match(['put', 'patch'], '/events/{event}', [ApiEventController::class, 'update']);
        Route::delete('/events/{event}', [ApiEventController::class, 'destroy']);
        Route::post('/events/{event}/cancel', [ApiEventController::class, 'cancel']);
        Route::post('/events/{event}/reschedule', [ApiEventController::class, 'reschedule']);
        Route::get('/events/{event}/registrations', [ApiEventRegistrationController::class, 'index']);
        Route::post('/events/{event}/registrations', [ApiEventRegistrationController::class, 'store']);
        Route::match(['put', 'patch'], '/events/{event}/registrations/{registration}', [ApiEventRegistrationController::class, 'update']);
        Route::delete('/events/{event}/registrations/{registration}', [ApiEventRegistrationController::class, 'destroy']);

        Route::apiResource('users', ApiUserController::class)->except('show');
        Route::get('/activity-logs', [ApiSystemLogController::class, 'index']);
    });
});

Route::post('/register', [EventRegistrationController::class, 'store'])
    ->middleware(EnsureFrontendIsAvailable::class);

Route::get('/maintenance-game/leaderboard', [MaintenanceGameScoreController::class, 'index'])
    ->middleware('throttle:30,1');

Route::post('/maintenance-game/scores', [MaintenanceGameScoreController::class, 'store'])
    ->middleware('throttle:8,1');
