<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use App\Services\GoogleCalendarService;
use App\Services\GoogleSheetsReader;
use App\Services\GoogleSheetsRegistrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Mockery\MockInterface;
use Tests\TestCase;

class GoogleIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_google_api_reports_configuration_and_requires_crm_authentication(): void
    {
        $this->getJson('/api/v1/google/status')->assertUnauthorized();

        $user = User::factory()->create(['role' => 'admin']);
        $token = $user->createToken('Test phone')->plainTextToken;

        config()->set('services.google.client_id', null);
        config()->set('services.google.client_secret', null);
        config()->set('services.google.redirect', null);

        $this->getJson('/api/v1/google/status', $this->headers($token))
            ->assertOk()
            ->assertJsonPath('data.configured', false)
            ->assertJsonPath('data.connected', false)
            ->assertJsonPath('data.account_email', null)
            ->assertJsonPath('data.services.0', 'calendar')
            ->assertJsonPath('data.services.1', 'sheets');
    }

    public function test_mobile_authorization_uses_one_time_signed_bridge(): void
    {
        config()->set('services.google.client_id', 'test-client');
        config()->set('services.google.client_secret', 'test-secret');
        config()->set('services.google.redirect', 'https://omc.test/google/callback');

        $user = User::factory()->create(['role' => 'editor']);
        $token = $user->createToken('Test phone')->plainTextToken;

        $url = $this->postJson('/api/v1/google/authorization', [], $this->headers($token))
            ->assertOk()
            ->json('data.authorization_url');

        $this->assertIsString($url);
        $this->assertStringContainsString('/google/mobile/', $url);
        $this->assertStringContainsString('signature=', $url);

        $this->get($url)
            ->assertRedirect()
            ->assertHeader('Location');

        $this->get($url)->assertGone();
    }

    public function test_google_tokens_are_encrypted_and_can_be_disconnected(): void
    {
        $user = User::factory()->create(['role' => 'content']);
        $user->forceFill([
            'google_token' => 'plain-access-token',
            'google_refresh_token' => 'plain-refresh-token',
            'google_token_expires_at' => now()->addHour(),
            'google_email' => 'worker@example.com',
        ])->save();

        $raw = DB::table('users')->where('id', $user->id)->first();
        $this->assertNotSame('plain-access-token', $raw->google_token);
        $this->assertNotSame('plain-refresh-token', $raw->google_refresh_token);
        $this->assertSame('plain-refresh-token', $user->fresh()->google_refresh_token);

        $token = $user->createToken('Test phone')->plainTextToken;
        $this->deleteJson('/api/v1/google', [], $this->headers($token))
            ->assertOk()
            ->assertJsonPath('message', 'Google відключено.');

        $user->refresh();
        $this->assertNull($user->google_token);
        $this->assertNull($user->google_refresh_token);
        $this->assertNull($user->google_email);
        $this->assertDatabaseHas('system_logs', [
            'user_id' => $user->id,
            'action' => 'disconnect_google',
        ]);
    }

    public function test_callback_without_valid_state_is_safe_and_web_connect_requires_login(): void
    {
        $this->get('/google/redirect')->assertRedirect('/login');
        $this->get('/google/callback')
            ->assertOk()
            ->assertSee('Запит Google прострочений або вже використаний.');
    }

    public function test_google_sheet_rows_are_imported_idempotently_and_updated(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $event = $this->externalEvent($user);

        $firstRows = [
            ['Позначка часу', 'ПІБ', 'Електронна пошта', 'Номер телефону'],
            ['03.11.2026 10:00', 'Іван Петренко', 'ivan@example.com', '+380501112233'],
            ['03.11.2026 10:01', 'Олена Коваль', 'olena@example.com', '0502223344'],
        ];
        $secondRows = [
            $firstRows[0],
            ['03.11.2026 10:00', 'Іван Петрович Петренко', 'ivan@example.com', '+380501112233'],
            $firstRows[2],
        ];

        $reader = $this->mock(GoogleSheetsReader::class);
        $reader->shouldReceive('read')->twice()->andReturn(
            $this->sheet($firstRows),
            $this->sheet($secondRows),
        );

        $service = app(GoogleSheetsRegistrationService::class);
        $first = $service->sync($user, $event);
        $second = $service->sync($user, $event->fresh());

        $this->assertSame(2, $first['imported']);
        $this->assertSame(0, $first['updated']);
        $this->assertSame(0, $second['imported']);
        $this->assertSame(2, $second['updated']);
        $this->assertDatabaseCount('registrations', 2);
        $this->assertDatabaseHas('registrations', [
            'event_id' => $event->id,
            'name' => 'Іван Петрович Петренко',
            'email' => 'ivan@example.com',
            'source' => 'google_form',
        ]);
        $this->assertNotNull($event->fresh()->google_last_synced_at);
        $this->assertSame($user->id, $event->fresh()->google_sync_user_id);
    }

    public function test_mobile_endpoint_syncs_google_registrations_and_logs_action(): void
    {
        $user = User::factory()->create(['role' => 'editor']);
        $event = $this->externalEvent($user);
        $token = $user->createToken('Test phone')->plainTextToken;

        $this->mock(GoogleSheetsReader::class)
            ->shouldReceive('read')
            ->once()
            ->andReturn($this->sheet([
                ['Timestamp', 'Full name', 'Email', 'Phone'],
                ['2026-11-03 11:00', 'Test Participant', 'test@example.com', '0500000000'],
            ]));

        $this->postJson(
            "/api/v1/events/{$event->id}/registrations/google-sync",
            [],
            $this->headers($token),
        )
            ->assertOk()
            ->assertJsonPath('data.imported', 1)
            ->assertJsonPath('data.updated', 0);

        $this->assertDatabaseHas('system_logs', [
            'user_id' => $user->id,
            'action' => 'sync_google_registrations',
            'source' => 'mobile_app',
        ]);
    }

    public function test_calendar_observer_handles_web_and_mobile_event_changes_once(): void
    {
        $this->mock(GoogleCalendarService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('createEvent')->once()->andReturnTrue();
            $mock->shouldReceive('updateEvent')->once()->andReturnTrue();
            $mock->shouldReceive('deleteEvent')->once()->andReturnTrue();
        });

        $user = User::factory()->create(['role' => 'editor']);
        $event = Event::query()->create([
            'title' => 'Calendar event',
            'description' => 'Test',
            'event_date' => now()->addDay(),
            'status' => 'published',
            'has_registration_button' => false,
            'registration_type' => 'none',
            'show_available_slots' => false,
            'user_id' => $user->id,
        ]);

        $event->update(['title' => 'Updated calendar event']);
        $event->update(['status' => 'cancelled']);
    }

    private function externalEvent(User $user): Event
    {
        return Event::query()->create([
            'title' => 'Google registrations',
            'description' => 'Test',
            'event_date' => now()->addDay(),
            'status' => 'published',
            'has_registration_button' => true,
            'registration_type' => 'external',
            'google_form_url' => 'https://docs.google.com/forms/d/test/viewform',
            'google_sheet_url' => 'https://docs.google.com/spreadsheets/d/sheet-id/edit',
            'show_available_slots' => false,
            'user_id' => $user->id,
        ]);
    }

    private function sheet(array $rows): array
    {
        return [
            'spreadsheet_id' => 'sheet-id',
            'sheet_name' => 'Form Responses 1',
            'rows' => $rows,
        ];
    }

    private function headers(string $token): array
    {
        return [
            'Authorization' => 'Bearer '.$token,
            'X-OMC-App' => 'flutter',
        ];
    }
}
