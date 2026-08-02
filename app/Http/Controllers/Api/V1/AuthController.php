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
            $this->logSecurityEvent(
                $request,
                $user,
                'mobile_login_failed',
                'Невдала спроба входу в мобільну CRM',
            );
            throw ValidationException::withMessages([
                'email' => ['Невірна електронна адреса або пароль.'],
            ]);
        }

        if (! in_array($user->role, ['admin', 'editor', 'content'], true)) {
            $this->logSecurityEvent(
                $request,
                $user,
                'mobile_login_denied',
                'Відмовлено у вході в мобільну CRM через відсутність CRM-ролі',
            );

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
                $this->logSecurityEvent(
                    $request,
                    $user,
                    'mobile_2fa_failed',
                    'Невдала перевірка коду 2FA під час входу в мобільну CRM',
                );

                return response()->json([
                    'message' => 'Невірний або вже використаний код 2FA.',
                    'two_factor_required' => true,
                    'errors' => ['two_factor_code' => ['Перевірте код у застосунку-автентифікаторі.']],
                ], 422);
            }
        }

        $newToken = $user->createToken(
            $request->string('device_name')->toString(),
            ['crm:access'],
            now()->addDays(30),
        );
        $token = $newToken->plainTextToken;

        $user->tokens()
            ->whereKeyNot($newToken->accessToken->getKey())
            ->latest()
            ->offset(4)
            ->limit(1000)
            ->get()
            ->each
            ->delete();

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
        $this->logSecurityEvent(
            $request,
            $request->user(),
            'mobile_logout',
            'Вихід із мобільної CRM на поточному пристрої',
        );
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

        $this->logSecurityEvent(
            $request,
            $request->user(),
            'mobile_session_revoked',
            'Завершено мобільну сесію: '.$session->name,
        );
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

        $this->logSecurityEvent(
            $request,
            $request->user(),
            'mobile_other_sessions_revoked',
            'Завершено інші мобільні сесії: '.$deletedCount,
        );

        return response()->json([
            'message' => 'Інші мобільні сесії завершено.',
            'data' => [
                'deleted_count' => $deletedCount,
            ],
        ]);
    }

    private function logSecurityEvent(
        Request $request,
        ?User $user,
        string $action,
        string $description,
    ): void {
        SystemLog::query()->create([
            'user_id' => $user?->id,
            'action' => $action,
            'description' => $description,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'source' => 'mobile_app',
            'device_name' => $request->string('device_name')->toString() ?: null,
            'platform' => $request->string('platform')->toString() ?: null,
        ]);
    }
}
