<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Resources\Api\V1\SessionResource;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\SystemLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::query()->where('email', $request->string('email'))->first();

        if (! $user || ! Hash::check($request->string('password'), $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Невірна електронна адреса або пароль.'],
            ]);
        }

        if (! in_array($user->role, ['admin', 'editor', 'content'], true)) {
            return response()->json([
                'message' => 'Цей користувач не має доступу до CRM.',
            ], 403);
        }

        if ($user->hasTwoFactorEnabled()) {
            $code = $request->string('two_factor_code')->toString();

            if ($code === '') {
                return response()->json([
                    'message' => 'Введіть код двофакторної автентифікації.',
                    'two_factor_required' => true,
                ], 422);
            }

            if (! $user->validateTwoFactorCode($code)) {
                return response()->json([
                    'message' => 'Невірний або вже використаний код 2FA.',
                    'two_factor_required' => true,
                    'errors' => ['two_factor_code' => ['Перевірте код у застосунку-автентифікаторі.']],
                ], 422);
            }
        }

        $token = $user->createToken($request->string('device_name'))->plainTextToken;

        $deviceParts = array_filter([
            $request->string('device_model')->toString(),
            $request->string('os_version')->toString(),
            $request->string('app_version')->isNotEmpty()
                ? 'ОМЦ CRM '.$request->string('app_version')->toString()
                : null,
        ]);

        SystemLog::query()->create([
            'user_id' => $user->id,
            'action' => 'mobile_login',
            'description' => 'Вхід у CRM з мобільного застосунку'.($deviceParts ? ': '.implode(', ', $deviceParts) : ''),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'source' => 'mobile_app',
            'device_name' => $request->string('device_name')->toString(),
            'platform' => $request->string('platform')->toString() ?: null,
        ]);

        return response()->json([
            'data' => [
                'user' => new UserResource($user),
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ]);
    }

    public function me(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Сесію на цьому пристрої завершено.',
        ]);
    }

    public function sessions(Request $request): AnonymousResourceCollection
    {
        $tokens = $request->user()
            ->tokens()
            ->latest()
            ->get();

        return SessionResource::collection($tokens);
    }

    public function destroySession(Request $request, int $token): JsonResponse
    {
        $session = $request->user()
            ->tokens()
            ->findOrFail($token);

        $session->delete();

        return response()->json([
            'message' => 'Мобільну сесію завершено.',
        ]);
    }

    public function destroyOtherSessions(Request $request): JsonResponse
    {
        $currentTokenId = $request->user()->currentAccessToken()->getKey();

        $deletedCount = $request->user()
            ->tokens()
            ->whereKeyNot($currentTokenId)
            ->delete();

        return response()->json([
            'message' => 'Інші мобільні сесії завершено.',
            'data' => [
                'deleted_count' => $deletedCount,
            ],
        ]);
    }
}
