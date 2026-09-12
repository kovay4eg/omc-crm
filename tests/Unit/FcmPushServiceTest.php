<?php

namespace Tests\Unit;

use App\Models\MobilePushDevice;
use App\Services\FcmPushService;
use ReflectionMethod;
use Tests\TestCase;

class FcmPushServiceTest extends TestCase
{
    public function test_android_messages_use_the_native_high_priority_notification_channel(): void
    {
        $message = $this->messageFor('android');

        $this->assertSame(
            ['title' => 'Перевірка', 'body' => 'Push працює'],
            $message['notification'],
        );
        $this->assertSame('high', $message['android']['priority']);
        $this->assertSame('86400s', $message['android']['ttl']);
        $this->assertSame(
            'omc_crm_high_importance',
            $message['android']['notification']['channel_id'],
        );
        $this->assertSame('default', $message['android']['notification']['sound']);
        $this->assertTrue($message['android']['notification']['default_vibrate_timings']);
        $this->assertSame('42', $message['data']['announcement_id']);
    }

    public function test_ios_messages_keep_native_notification_payload(): void
    {
        $message = $this->messageFor('ios');

        $this->assertSame(
            ['title' => 'Перевірка', 'body' => 'Push працює'],
            $message['notification'],
        );
        $this->assertSame('10', $message['apns']['headers']['apns-priority']);
        $this->assertSame('42', $message['data']['announcement_id']);
    }

    private function messageFor(string $platform): array
    {
        $device = new MobilePushDevice([
            'token' => 'test-token',
            'platform' => $platform,
        ]);
        $method = new ReflectionMethod(FcmPushService::class, 'messageForDevice');

        return $method->invoke(
            app(FcmPushService::class),
            $device,
            'Перевірка',
            'Push працює',
            ['announcement_id' => 42],
        );
    }
}
