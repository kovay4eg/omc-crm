<?php

namespace Tests\Feature\Api\V1;

use App\Models\AdminProAssignment;
use App\Models\AdminProMailCheckpoint;
use App\Models\MobilePushDevice;
use App\Models\User;
use App\Services\AdminProMailAccessService;
use App\Services\AdminProMailboxService;
use App\Services\FcmPushService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Mockery\MockInterface;
use Tests\TestCase;

class AdminProMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_mailbox_is_available_to_admin_pro_and_explicitly_granted_users(): void
    {
        $adminPro = User::factory()->create(['role' => 'admin']);
        $ordinaryAdmin = User::factory()->create(['role' => 'admin']);
        $editor = User::factory()->create(['role' => 'editor']);
        AdminProAssignment::transferTo($adminPro);
        app(AdminProMailAccessService::class)->setAccess($adminPro, $editor, true);

        $this->mock(AdminProMailboxService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('status')->twice()->andReturn([
                'address' => 'post@omc.pl.ua',
                'configured' => true,
            ]);
            $mock->shouldReceive('folders')->twice()->andReturn([]);
        });

        Sanctum::actingAs($ordinaryAdmin);
        $this->getJson('/api/v1/admin-pro/mail/status')->assertForbidden();
        $this->getJson('/api/v1/admin-pro/mail/messages')->assertForbidden();
        $this->getJson('/api/v1/admin-pro/mail/messages/1')->assertForbidden();
        $this->postJson('/api/v1/admin-pro/mail/messages', [])->assertForbidden();
        $this->patchJson('/api/v1/admin-pro/mail/messages/1', [])->assertForbidden();
        $this->deleteJson('/api/v1/admin-pro/mail/messages/1')->assertForbidden();

        Sanctum::actingAs($adminPro);
        $this->getJson('/api/v1/admin-pro/mail/status')
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertJsonPath('data.address', 'post@omc.pl.ua')
            ->assertJsonPath('data.configured', true);

        Sanctum::actingAs($editor);
        $this->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('data.mail_access', true);
        $this->getJson('/api/v1/admin-pro/mail/status')->assertOk();

        $this->expectException(AuthorizationException::class);
        app(AdminProMailAccessService::class)->setAccess($ordinaryAdmin, $editor, false);
    }

    public function test_admin_pro_can_read_and_send_without_logging_message_content(): void
    {
        $adminPro = User::factory()->create(['role' => 'admin']);
        AdminProAssignment::transferTo($adminPro);
        $secretSubject = 'Конфіденційна тема';
        $secretBody = 'Текст, якого не має бути в журналі.';

        $this->mock(AdminProMailboxService::class, function (MockInterface $mock) use ($secretSubject, $secretBody): void {
            $mock->shouldReceive('messages')->once()->with(1, 30, null, 'inbox', 'all')->andReturn([
                'data' => [[
                    'uid' => 7,
                    'subject' => 'Вхідний лист',
                    'from_name' => 'Apple',
                    'from_address' => 'noreply@apple.com',
                    'date' => now()->toAtomString(),
                    'read' => false,
                    'flagged' => false,
                    'folder' => 'inbox',
                ]],
                'meta' => ['current_page' => 1, 'per_page' => 30, 'total' => 1, 'last_page' => 1],
            ]);
            $mock->shouldReceive('send')
                ->once()
                ->with(['receiver@example.com'], $secretSubject, $secretBody);
        });

        Sanctum::actingAs($adminPro);
        $this->getJson('/api/v1/admin-pro/mail/messages')
            ->assertOk()
            ->assertJsonPath('data.0.uid', 7);
        $this->postJson('/api/v1/admin-pro/mail/messages', [
            'to' => ['receiver@example.com'],
            'subject' => $secretSubject,
            'body' => $secretBody,
        ])->assertCreated();

        $this->assertDatabaseHas('system_logs', [
            'action' => 'admin_pro_mail_send',
            'description' => 'Користувач із поштовим доступом надіслав лист через post@omc.pl.ua.',
        ]);
        $this->assertDatabaseMissing('system_logs', ['description' => $secretSubject]);
        $this->assertDatabaseMissing('system_logs', ['description' => $secretBody]);
    }

    public function test_web_mail_page_is_restricted_and_renders_its_responsive_layout(): void
    {
        $adminPro = User::factory()->create(['role' => 'admin']);
        $ordinaryAdmin = User::factory()->create(['role' => 'admin']);
        AdminProAssignment::transferTo($adminPro);

        $this->actingAs($ordinaryAdmin)
            ->get('/admin/admin-pro-mail')
            ->assertForbidden();

        $this->mock(AdminProMailboxService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('configured')->once()->andReturn(true);
            $mock->shouldReceive('folders')->once()->andReturn([
                ['key' => 'inbox', 'label' => 'Вхідні', 'total' => 0, 'unread' => 0],
                ['key' => 'archive', 'label' => 'Архів', 'total' => 0, 'unread' => 0],
                ['key' => 'spam', 'label' => 'Спам', 'total' => 0, 'unread' => 0],
                ['key' => 'trash', 'label' => 'Видалені', 'total' => 0, 'unread' => 0],
            ]);
            $mock->shouldReceive('messages')->once()->with(1, 30, '', 'inbox', 'all')->andReturn([
                'data' => [],
                'meta' => ['current_page' => 1, 'per_page' => 30, 'total' => 0, 'last_page' => 1],
            ]);
        });

        $this->actingAs($adminPro)
            ->get('/admin/admin-pro-mail')
            ->assertOk()
            ->assertSee('post@omc.pl.ua')
            ->assertSee('omc-mail__layout', false)
            ->assertSee('Вхідні')
            ->assertSee('Архів')
            ->assertSee('Спам')
            ->assertSee('Видалені')
            ->assertSee('Увімкнути сповіщення')
            ->assertSee('@media (max-width: 640px)', false);
    }

    public function test_admin_pro_can_register_a_private_web_push_device(): void
    {
        $adminPro = User::factory()->create(['role' => 'admin']);
        $ordinaryAdmin = User::factory()->create(['role' => 'admin']);
        AdminProAssignment::transferTo($adminPro);
        $token = str_repeat('w', 64);

        $this->actingAs($ordinaryAdmin)
            ->putJson('/admin/admin-pro/mail/push-device', ['token' => $token])
            ->assertForbidden();

        $this->actingAs($adminPro)
            ->putJson('/admin/admin-pro/mail/push-device', ['token' => $token])
            ->assertOk();

        $this->assertDatabaseHas('mobile_push_devices', [
            'user_id' => $adminPro->id,
            'token_hash' => hash('sha256', $token),
            'platform' => 'web',
        ]);
        $device = MobilePushDevice::query()->firstOrFail();
        $this->assertSame($token, $device->token);
        $this->assertTrue($device->preferences['push_mail']);
    }

    public function test_mail_checker_notifies_users_with_mail_access_without_exposing_message_body(): void
    {
        $adminPro = User::factory()->create(['role' => 'admin']);
        AdminProAssignment::transferTo($adminPro);
        AdminProMailCheckpoint::query()->create([
            'mailbox' => 'post@omc.pl.ua',
            'uid_validity' => 10,
            'last_uid' => 40,
        ]);
        config()->set('admin_pro_mail.address', 'post@omc.pl.ua');

        $this->mock(AdminProMailboxService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('configured')->once()->andReturn(true);
            $mock->shouldReceive('newMessageBatch')->once()->with(40, 10)->andReturn([
                'uid_validity' => 10,
                'last_uid' => 41,
                'reset' => false,
                'messages' => [[
                    'uid' => 41,
                    'subject' => 'Новий документ',
                    'from_name' => 'Apple',
                    'from_address' => 'sender@example.com',
                    'date' => now()->toAtomString(),
                    'read' => false,
                    'flagged' => false,
                    'folder' => 'inbox',
                ]],
            ]);
        });
        $this->mock(FcmPushService::class, function (MockInterface $mock) use ($adminPro): void {
            $mock->shouldReceive('sendToUser')
                ->once()
                ->withArgs(fn (User $user, string $title, string $body, array $data, string $preference): bool => $user->is($adminPro)
                    && $title === 'Новий лист від Apple'
                    && $body === 'Новий документ'
                    && $data['type'] === 'admin_pro_mail'
                    && $preference === 'push_mail')
                ->andReturn(['sent' => 1, 'failed' => 0, 'skipped' => false]);
        });

        $this->artisan('admin-pro-mail:check')->assertSuccessful();
        $this->assertDatabaseHas('admin_pro_mail_checkpoints', ['last_uid' => 41]);
    }
}
