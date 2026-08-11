<?php

namespace Tests\Feature\Api\V1;

use App\Models\AdminProAssignment;
use App\Models\User;
use App\Services\AdminProMailboxService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Mockery\MockInterface;
use Tests\TestCase;

class AdminProMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_mailbox_is_available_only_to_current_admin_pro(): void
    {
        $adminPro = User::factory()->create(['role' => 'admin']);
        $ordinaryAdmin = User::factory()->create(['role' => 'admin']);
        $editor = User::factory()->create(['role' => 'editor']);
        AdminProAssignment::transferTo($adminPro);

        $this->mock(AdminProMailboxService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('status')->once()->andReturn([
                'address' => 'post@omc.pl.ua',
                'configured' => true,
            ]);
        });

        Sanctum::actingAs($ordinaryAdmin);
        $this->getJson('/api/v1/admin-pro/mail/status')->assertForbidden();
        $this->getJson('/api/v1/admin-pro/mail/messages')->assertForbidden();
        $this->getJson('/api/v1/admin-pro/mail/messages/1')->assertForbidden();
        $this->postJson('/api/v1/admin-pro/mail/messages', [])->assertForbidden();
        $this->patchJson('/api/v1/admin-pro/mail/messages/1', [])->assertForbidden();
        $this->deleteJson('/api/v1/admin-pro/mail/messages/1')->assertForbidden();

        Sanctum::actingAs($editor);
        $this->getJson('/api/v1/admin-pro/mail/status')->assertForbidden();

        Sanctum::actingAs($adminPro);
        $this->getJson('/api/v1/admin-pro/mail/status')
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertJsonPath('data.address', 'post@omc.pl.ua')
            ->assertJsonPath('data.configured', true);
    }

    public function test_admin_pro_can_read_and_send_without_logging_message_content(): void
    {
        $adminPro = User::factory()->create(['role' => 'admin']);
        AdminProAssignment::transferTo($adminPro);
        $secretSubject = 'Конфіденційна тема';
        $secretBody = 'Текст, якого не має бути в журналі.';

        $this->mock(AdminProMailboxService::class, function (MockInterface $mock) use ($secretSubject, $secretBody): void {
            $mock->shouldReceive('messages')->once()->andReturn([
                'data' => [[
                    'uid' => 7,
                    'subject' => 'Вхідний лист',
                    'from_name' => 'Apple',
                    'from_address' => 'noreply@apple.com',
                    'date' => now()->toAtomString(),
                    'read' => false,
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
            'description' => 'AdminPro надіслав лист через post@omc.pl.ua.',
        ]);
        $this->assertDatabaseMissing('system_logs', ['description' => $secretSubject]);
        $this->assertDatabaseMissing('system_logs', ['description' => $secretBody]);
    }
}
