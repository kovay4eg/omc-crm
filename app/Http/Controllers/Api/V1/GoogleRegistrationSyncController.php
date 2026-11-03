<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Services\GoogleSheetsRegistrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class GoogleRegistrationSyncController extends Controller
{
    public function __invoke(
        Request $request,
        Event $event,
        GoogleSheetsRegistrationService $sheets,
    ): JsonResponse {
        if ($event->registration_type !== 'external' || blank($event->google_sheet_url)) {
            throw ValidationException::withMessages([
                'google_sheet_url' => ['Для події не налаштовано таблицю відповідей Google Forms.'],
            ]);
        }

        $result = $sheets->sync($request->user(), $event);
        system_log(
            'sync_google_registrations',
            "Синхронізовано заявки для «{$event->title}»: додано {$result['imported']}, оновлено {$result['updated']}.",
        );

        return response()->json([
            'message' => 'Реєстрації синхронізовано.',
            'data' => $result,
        ]);
    }
}
