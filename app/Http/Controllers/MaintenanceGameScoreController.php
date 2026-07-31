<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceGameScore;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MaintenanceGameScoreController extends Controller
{
    public function index(): JsonResponse
    {
        $this->ensureMaintenanceModeIsEnabled();

        return response()->json([
            'scores' => $this->leaderboard(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->ensureMaintenanceModeIsEnabled();

        $validated = $request->validate([
            'nickname' => ['required', 'string', 'max:30'],
            'score' => ['required', 'integer', 'min:0', 'max:500'],
        ]);

        $nickname = trim(preg_replace('/\s+/', ' ', strip_tags($validated['nickname'])));

        if ($nickname === '') {
            throw ValidationException::withMessages([
                'nickname' => 'Введіть нікнейм для таблиці рекордів.',
            ]);
        }

        MaintenanceGameScore::create([
            'nickname' => $nickname,
            'score' => $validated['score'],
        ]);

        return response()->json([
            'message' => 'Результат збережено.',
            'scores' => $this->leaderboard(),
        ], JsonResponse::HTTP_CREATED);
    }

    private function ensureMaintenanceModeIsEnabled(): void
    {
        abort_unless(
            (bool) SiteSetting::query()->value('maintenance_mode'),
            404,
        );
    }

    private function leaderboard(): array
    {
        return MaintenanceGameScore::query()
            ->orderByDesc('score')
            ->orderBy('created_at')
            ->limit(10)
            ->get(['nickname', 'score'])
            ->map(fn (MaintenanceGameScore $score) => [
                'nickname' => $score->nickname,
                'score' => $score->score,
            ])
            ->all();
    }
}
