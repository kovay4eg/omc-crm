<?php

use App\Http\Controllers\Api\V1\AppAnnouncementController as ApiAppAnnouncementController;
use App\Http\Controllers\Api\V1\AppStateController as ApiAppStateController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ContentDocumentController as ApiContentDocumentController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\EventController as ApiEventController;
use App\Http\Controllers\Api\V1\EventRegistrationController as ApiEventRegistrationController;
use App\Http\Controllers\Api\V1\EventSummaryController as ApiEventSummaryController;
use App\Http\Controllers\Api\V1\HomepageSettingsController as ApiHomepageSettingsController;
use App\Http\Controllers\Api\V1\PartnerController as ApiPartnerController;
use App\Http\Controllers\Api\V1\SystemLogController as ApiSystemLogController;
use App\Http\Controllers\Api\V1\TeamController as ApiTeamController;
use App\Http\Controllers\Api\V1\TwoFactorController as ApiTwoFactorController;
use App\Http\Controllers\Api\V1\UserController as ApiUserController;
use App\Http\Controllers\EventRegistrationController;
use App\Http\Controllers\MaintenanceGameScoreController;
use App\Http\Middleware\EnsureCrmApiAccess;
use App\Http\Middleware\EnsureFrontendIsAvailable;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login'])
        ->middleware('throttle:crm-login');

    Route::middleware(['auth:sanctum', EnsureCrmApiAccess::class, 'throttle:crm-api'])->group(function () {
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

        Route::get('/event-summaries', [ApiEventSummaryController::class, 'index']);
        Route::post('/events/{event}/summary', [ApiEventSummaryController::class, 'update']);
        Route::delete('/event-summary-images/{image}', [ApiEventSummaryController::class, 'destroyImage']);
        Route::patch('/event-summaries/{summary}/images/reorder', [ApiEventSummaryController::class, 'reorderImages']);

        Route::get('/homepage-settings', [ApiHomepageSettingsController::class, 'show']);
        Route::post('/homepage-settings', [ApiHomepageSettingsController::class, 'update']);

        Route::get('/partners', [ApiPartnerController::class, 'index']);
        Route::post('/partners', [ApiPartnerController::class, 'store']);
        Route::delete('/partners/{index}', [ApiPartnerController::class, 'destroy'])->whereNumber('index');

        Route::get('/content-documents/{type}', [ApiContentDocumentController::class, 'index']);
        Route::post('/content-documents/{type}', [ApiContentDocumentController::class, 'store']);
        Route::post('/content-documents/{type}/{document}', [ApiContentDocumentController::class, 'update'])
            ->whereNumber('document');
        Route::delete('/content-documents/{type}/{document}', [ApiContentDocumentController::class, 'destroy'])
            ->whereNumber('document');

        Route::get('/team', [ApiTeamController::class, 'index']);
        Route::get('/team/options', [ApiTeamController::class, 'options']);
        Route::post('/team', [ApiTeamController::class, 'store']);
        Route::patch('/team/reorder', [ApiTeamController::class, 'reorder']);
        Route::post('/team/banner', [ApiTeamController::class, 'updateBanner']);
        Route::post('/team/{employee}', [ApiTeamController::class, 'update']);
        Route::delete('/team/{employee}', [ApiTeamController::class, 'destroy']);

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
