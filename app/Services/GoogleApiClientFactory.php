<?php

namespace App\Services;

use App\Models\User;
use Google\Client;
use RuntimeException;

class GoogleApiClientFactory
{
    public function make(User $user): Client
    {
        if (! $user->hasGoogleConnection()) {
            throw new RuntimeException('Google не підключено до цього профілю.');
        }

        if (! $this->configured()) {
            throw new RuntimeException('Google OAuth не налаштовано на сервері.');
        }

        $client = new Client;
        $client->setClientId((string) config('services.google.client_id'));
        $client->setClientSecret((string) config('services.google.client_secret'));
        $client->setRedirectUri((string) config('services.google.redirect'));

        $remainingLifetime = $user->google_token_expires_at
            ? max(0, now()->diffInSeconds($user->google_token_expires_at, false))
            : 0;

        $client->setAccessToken([
            'access_token' => $user->google_token,
            'refresh_token' => $user->google_refresh_token,
            'expires_in' => $remainingLifetime,
            'created' => now()->timestamp,
        ]);

        if (! $client->isAccessTokenExpired()) {
            return $client;
        }

        $token = $client->fetchAccessTokenWithRefreshToken($user->google_refresh_token);

        if (isset($token['error']) || blank($token['access_token'] ?? null)) {
            throw new RuntimeException('Google відкликав доступ. Підключіть обліковий запис повторно.');
        }

        $user->forceFill([
            'google_token' => $token['access_token'],
            'google_refresh_token' => $token['refresh_token'] ?? $user->google_refresh_token,
            'google_token_expires_at' => now()->addSeconds((int) ($token['expires_in'] ?? 3600)),
        ])->save();

        return $client;
    }

    public function configured(): bool
    {
        return filled(config('services.google.client_id'))
            && filled(config('services.google.client_secret'))
            && filled(config('services.google.redirect'));
    }
}
