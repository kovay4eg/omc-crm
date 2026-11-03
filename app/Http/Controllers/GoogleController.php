<?php

namespace App\Http\Controllers;

use App\Models\SystemLog;
use App\Models\User;
use App\Services\GoogleApiClientFactory;
use Google\Service\Calendar;
use Google\Service\Sheets;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;
use RuntimeException;
use Throwable;

class GoogleController extends Controller
{
    public function __construct(private readonly GoogleApiClientFactory $clients) {}

    public function redirect(Request $request): RedirectResponse
    {
        abort_unless($this->clients->configured(), 503, 'Google OAuth ще не налаштовано.');

        return $this->start($request->user(), 'web');
    }

    public function authorization(Request $request): JsonResponse
    {
        abort_unless($this->clients->configured(), 503, 'Google OAuth ще не налаштовано.');

        $token = Str::random(64);
        Cache::put($this->mobileTokenKey($token), $request->user()->getKey(), now()->addMinutes(5));

        return response()->json([
            'data' => [
                'authorization_url' => URL::temporarySignedRoute(
                    'google.mobile.redirect',
                    now()->addMinutes(5),
                    ['token' => $token],
                ),
            ],
        ]);
    }

    public function mobileRedirect(Request $request, string $token): RedirectResponse
    {
        $userId = Cache::pull($this->mobileTokenKey($token));
        abort_if(blank($userId), 410, 'Посилання підключення прострочене.');

        return $this->start(User::query()->findOrFail($userId), 'mobile');
    }

    public function callback(Request $request): View
    {
        $state = (string) $request->query('state');
        $pending = $state === '' ? null : Cache::pull($this->stateKey($state));

        if (! is_array($pending) || blank($pending['user_id'] ?? null)) {
            return view('google-result', [
                'success' => false,
                'message' => 'Запит Google прострочений або вже використаний. Спробуйте підключення ще раз.',
                'mobile' => false,
            ]);
        }

        $mobile = ($pending['client'] ?? null) === 'mobile';

        if ($request->filled('error')) {
            return view('google-result', [
                'success' => false,
                'message' => 'Підключення Google скасовано.',
                'mobile' => $mobile,
            ]);
        }

        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            $user = User::query()->findOrFail($pending['user_id']);
            $refreshToken = $googleUser->refreshToken ?: $user->google_refresh_token;

            if (blank($refreshToken)) {
                throw new RuntimeException('Google не повернув ключ тривалого доступу.');
            }

            $user->forceFill([
                'google_token' => $googleUser->token,
                'google_refresh_token' => $refreshToken,
                'google_token_expires_at' => now()->addSeconds((int) ($googleUser->expiresIn ?? 3600)),
                'google_email' => $googleUser->getEmail(),
            ])->save();

            $this->log($user, 'connect_google', 'Підключено Google Calendar і таблиці відповідей.');

            return view('google-result', [
                'success' => true,
                'message' => 'Google Calendar і таблиці відповідей підключено.',
                'mobile' => $mobile,
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return view('google-result', [
                'success' => false,
                'message' => 'Не вдалося завершити підключення Google. Перевірте дозволи й повторіть спробу.',
                'mobile' => $mobile,
            ]);
        }
    }

    public function status(Request $request): JsonResponse
    {
        return response()->json([
            'data' => [
                'configured' => $this->clients->configured(),
                'connected' => $request->user()->hasGoogleConnection(),
                'account_email' => $request->user()->google_email,
                'services' => ['calendar', 'sheets'],
            ],
        ]);
    }

    public function disconnect(Request $request): JsonResponse|RedirectResponse
    {
        $user = $request->user();
        $user->forceFill([
            'google_token' => null,
            'google_refresh_token' => null,
            'google_token_expires_at' => null,
            'google_email' => null,
        ])->save();

        $this->log($user, 'disconnect_google', 'Відключено Google Calendar і таблиці відповідей.');

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Google відключено.']);
        }

        return redirect('/admin')->with('success', 'Google відключено');
    }

    private function start(User $user, string $client): RedirectResponse
    {
        $state = Str::random(64);
        Cache::put($this->stateKey($state), [
            'user_id' => $user->getKey(),
            'client' => $client,
        ], now()->addMinutes(10));

        return Socialite::driver('google')
            ->stateless()
            ->scopes([
                Calendar::CALENDAR_EVENTS,
                Sheets::SPREADSHEETS_READONLY,
            ])
            ->with([
                'access_type' => 'offline',
                'prompt' => 'consent',
                'include_granted_scopes' => 'true',
                'state' => $state,
            ])
            ->redirect();
    }

    private function stateKey(string $state): string
    {
        return 'google:oauth-state:'.$state;
    }

    private function mobileTokenKey(string $token): string
    {
        return 'google:mobile-token:'.$token;
    }

    private function log(User $user, string $action, string $description): void
    {
        SystemLog::query()->create([
            'user_id' => $user->getKey(),
            'action' => $action,
            'description' => $description,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'source' => 'system',
        ]);
    }
}
