<?php

namespace App\Services;

use App\Models\AppAnnouncement;
use App\Models\MobilePushDevice;
use App\Models\User;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class FcmPushService
{
    public function configured(): bool
    {
        return $this->projectId() !== null && $this->credentialsPath() !== null;
    }

    public function sendAnnouncement(AppAnnouncement $announcement): array
    {
        if (! $this->configured()) {
            return ['sent' => 0, 'failed' => 0, 'skipped' => true];
        }

        $credentials = new ServiceAccountCredentials(
            ['https://www.googleapis.com/auth/firebase.messaging'],
            $this->credentialsPath(),
        );
        $authToken = $credentials->fetchAuthToken()['access_token'] ?? null;
        if (! is_string($authToken) || $authToken === '') {
            return ['sent' => 0, 'failed' => 1, 'skipped' => false];
        }

        $result = ['sent' => 0, 'failed' => 0, 'skipped' => false];
        MobilePushDevice::query()
            ->where(fn ($query) => $query
                ->whereNull('preferences')
                ->orWhereNull('preferences->push_system')
                ->orWhereJsonContains('preferences->push_system', true))
            ->chunkById(100, function ($devices) use ($announcement, $authToken, &$result): void {
                foreach ($devices as $device) {
                    $response = $this->sendToDevice($announcement, $device, $authToken);
                    if ($response->successful()) {
                        $result['sent']++;

                        continue;
                    }
                    $result['failed']++;
                    if ($this->isUnregistered($response)) {
                        $device->delete();
                    }
                }
            });

        $announcement->forceFill(['push_sent_at' => now()])->save();

        return $result;
    }

    public function sendToUser(User $user, string $title, string $body, array $data = []): array
    {
        if (! $this->configured()) {
            return ['sent' => 0, 'failed' => 0, 'skipped' => true];
        }

        $credentials = new ServiceAccountCredentials(
            ['https://www.googleapis.com/auth/firebase.messaging'],
            $this->credentialsPath(),
        );
        $authToken = $credentials->fetchAuthToken()['access_token'] ?? null;
        if (! is_string($authToken) || $authToken === '') {
            return ['sent' => 0, 'failed' => 1, 'skipped' => false];
        }

        $result = ['sent' => 0, 'failed' => 0, 'skipped' => false];
        MobilePushDevice::query()
            ->where('user_id', $user->id)
            ->where(fn ($query) => $query
                ->whereNull('preferences')
                ->orWhereNull('preferences->push_support')
                ->orWhereJsonContains('preferences->push_support', true))
            ->each(function (MobilePushDevice $device) use ($title, $body, $data, $authToken, &$result): void {
                $response = Http::withToken($authToken)
                    ->acceptJson()
                    ->timeout(15)
                    ->retry(2, 250)
                    ->post('https://fcm.googleapis.com/v1/projects/'.$this->projectId().'/messages:send', [
                        'message' => [
                            'token' => $device->token,
                            'notification' => ['title' => $title, 'body' => $body],
                            'data' => collect($data)->map(fn (mixed $value): string => (string) $value)->all(),
                            'apns' => [
                                'headers' => ['apns-priority' => '10'],
                                'payload' => ['aps' => ['sound' => 'default', 'badge' => 1]],
                            ],
                            'android' => [
                                'priority' => 'high',
                                'notification' => ['sound' => 'default'],
                            ],
                        ],
                    ]);
                $response->successful() ? $result['sent']++ : $result['failed']++;
                if ($this->isUnregistered($response)) {
                    $device->delete();
                }
            });

        return $result;
    }

    private function sendToDevice(
        AppAnnouncement $announcement,
        MobilePushDevice $device,
        string $authToken,
    ): Response {
        return Http::withToken($authToken)
            ->acceptJson()
            ->timeout(15)
            ->retry(2, 250)
            ->post(
                'https://fcm.googleapis.com/v1/projects/'.$this->projectId().'/messages:send',
                [
                    'message' => [
                        'token' => $device->token,
                        'notification' => [
                            'title' => $announcement->title,
                            'body' => str($announcement->body)->stripTags()->limit(220)->toString(),
                        ],
                        'data' => [
                            'type' => 'app_announcement',
                            'announcement_id' => (string) $announcement->id,
                            'link_url' => (string) ($announcement->link_url ?? ''),
                        ],
                        'apns' => [
                            'headers' => ['apns-priority' => '10'],
                            'payload' => [
                                'aps' => [
                                    'sound' => 'default',
                                    'badge' => 1,
                                ],
                            ],
                        ],
                        'android' => [
                            'priority' => 'high',
                            'notification' => ['sound' => 'default'],
                        ],
                    ],
                ],
            );
    }

    private function isUnregistered(Response $response): bool
    {
        return collect($response->json('error.details', []))
            ->contains(fn (mixed $detail): bool => is_array($detail) && ($detail['errorCode'] ?? null) === 'UNREGISTERED');
    }

    private function projectId(): ?string
    {
        $value = config('services.firebase.project_id');

        return is_string($value) && $value !== '' ? $value : null;
    }

    private function credentialsPath(): ?string
    {
        $configured = config('services.firebase.credentials');
        if (! is_string($configured) || $configured === '') {
            return null;
        }
        $path = str_starts_with($configured, '/') || preg_match('/^[A-Za-z]:[\\\\\/]/', $configured)
            ? $configured
            : base_path($configured);

        return is_file($path) ? $path : null;
    }
}
