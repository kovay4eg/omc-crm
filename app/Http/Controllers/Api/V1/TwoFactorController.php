<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class TwoFactorController extends Controller
{
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json(['data' => [
            'enabled' => $user->hasTwoFactorEnabled(),
            'recovery_codes_left' => $user->hasTwoFactorEnabled()
                ? $user->getRecoveryCodes()->whereNull('used_at')->count()
                : 0,
        ]]);
    }

    public function prepare(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_if($user->hasTwoFactorEnabled(), 422, 'Двофакторна автентифікація вже увімкнена.');

        $secret = $user->createTwoFactorAuth();
        system_log('prepare_two_factor', 'Розпочато налаштування двофакторної автентифікації.');

        return response()->json(['data' => [
            'secret' => $secret->toString(),
            'uri' => $secret->toUri(),
        ]]);
    }

    public function confirm(Request $request): JsonResponse
    {
        $data = $request->validate(['code' => ['required', 'digits:6']]);
        $user = $request->user();

        if (! $user->confirmTwoFactorAuth($data['code'])) {
            throw ValidationException::withMessages(['code' => ['Невірний код. Перевірте час на пристрої та спробуйте ще раз.']]);
        }

        system_log('enable_two_factor', 'Увімкнено двофакторну автентифікацію.');

        return response()->json([
            'message' => 'Двофакторну автентифікацію увімкнено.',
            'data' => ['recovery_codes' => $this->codes($user)],
        ]);
    }

    public function regenerate(Request $request): JsonResponse
    {
        $data = $this->validateProtectedAction($request);
        $user = $request->user();
        abort_unless($user->hasTwoFactorEnabled(), 422, 'Двофакторна автентифікація не увімкнена.');
        abort_unless(Hash::check($data['password'], $user->password), 422, 'Невірний пароль.');
        abort_unless($user->validateTwoFactorCode($data['code'], false), 422, 'Невірний код 2FA.');

        $user->generateRecoveryCodes();
        system_log('regenerate_two_factor_codes', 'Оновлено резервні коди 2FA.');

        return response()->json(['data' => ['recovery_codes' => $this->codes($user)]]);
    }

    public function disable(Request $request): JsonResponse
    {
        $data = $this->validateProtectedAction($request);
        $user = $request->user();
        abort_unless(Hash::check($data['password'], $user->password), 422, 'Невірний пароль.');
        abort_unless($user->validateTwoFactorCode($data['code']), 422, 'Невірний код 2FA.');

        $user->disableTwoFactorAuth();
        system_log('disable_two_factor', 'Вимкнено двофакторну автентифікацію.');

        return response()->json(['message' => 'Двофакторну автентифікацію вимкнено.']);
    }

    private function validateProtectedAction(Request $request): array
    {
        return $request->validate([
            'password' => ['required', 'string'],
            'code' => ['required', 'string', 'min:6', 'max:32'],
        ]);
    }

    private function codes(User $user): array
    {
        return $user->getRecoveryCodes()->pluck('code')->values()->all();
    }
}
