<?php

namespace Tests\Feature\Api\V1;

use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class EventTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-07-31 12:00:00', 'Europe/Kyiv'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_event_list_is_protected_and_supports_filters(): void
    {
        $this->getJson('/api/v1/events')->assertUnauthorized();

        $user = User::factory()->create(['role' => 'editor']);
        $this->createEvent($user, [
            'title' => 'Опублікований тренінг',
            'status' => EventStatus::Published->value,
        ]);
        $this->createEvent($user, [
            'title' => 'Чернетка зустрічі',
            'status' => EventStatus::Draft->value,
        ]);
        $this->createEvent($user, [
            'title' => 'Минулий тренінг',
            'event_date' => now()->subDay(),
            'status' => EventStatus::Published->value,
        ]);

        $response = $this->getJson('/api/v1/events?search=тренінг&status=published', $this->headers($user));

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Опублікований тренінг')
            ->assertJsonStructure(['links', 'meta']);
    }

    public function test_crm_user_can_create_event_and_history(): void
    {
        $user = User::factory()->create(['role' => 'content']);

        $this->postJson('/api/v1/events', [
            'title' => 'Нова подія',
            'description' => 'Опис нової події',
            'event_date' => now()->addDay()->toIso8601String(),
            'has_registration_button' => true,
            'registration_type' => 'internal',
            'max_participants' => 25,
            'show_available_slots' => true,
            'status' => 'draft',
        ], $this->headers($user))
            ->assertCreated()
            ->assertJsonPath('data.title', 'Нова подія')
            ->assertJsonPath('data.author.id', $user->id);

        $this->assertDatabaseHas('events', [
            'title' => 'Нова подія',
            'user_id' => $user->id,
        ]);
        $this->assertDatabaseHas('event_histories', [
            'action' => 'created',
            'user_id' => $user->id,
        ]);
    }

    public function test_non_admin_cannot_change_event_date_during_regular_edit(): void
    {
        $editor = User::factory()->create(['role' => 'editor']);
        $event = $this->createEvent($editor);

        $this->patchJson('/api/v1/events/'.$event->id, [
            'event_date' => now()->addWeek()->toIso8601String(),
        ], $this->headers($editor))->assertUnprocessable();

        $this->assertTrue($event->fresh()->event_date->equalTo($event->event_date));
    }

    public function test_admin_can_edit_and_delete_event(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $event = $this->createEvent($admin);

        $this->patchJson('/api/v1/events/'.$event->id, [
            'title' => 'Оновлена назва',
            'event_date' => now()->addWeek()->toIso8601String(),
        ], $this->headers($admin))
            ->assertOk()
            ->assertJsonPath('data.title', 'Оновлена назва');

        $this->deleteJson('/api/v1/events/'.$event->id, [], $this->headers($admin))
            ->assertNoContent();

        $this->assertDatabaseMissing('events', ['id' => $event->id]);
    }

    public function test_only_admin_can_delete_event(): void
    {
        $editor = User::factory()->create(['role' => 'editor']);
        $event = $this->createEvent($editor);

        $this->deleteJson('/api/v1/events/'.$event->id, [], $this->headers($editor))
            ->assertForbidden();

        $this->assertDatabaseHas('events', ['id' => $event->id]);
    }

    public function test_event_can_be_cancelled_and_rescheduled_with_history(): void
    {
        $user = User::factory()->create(['role' => 'content']);
        $event = $this->createEvent($user, ['status' => EventStatus::Published->value]);
        $newDate = now()->addWeek();

        $this->postJson('/api/v1/events/'.$event->id.'/reschedule', [
            'new_date' => $newDate->toIso8601String(),
            'reason' => 'Зміна розкладу',
            'reschedule_public' => true,
        ], $this->headers($user))
            ->assertOk()
            ->assertJsonPath('data.status', 'rescheduled');

        $this->postJson('/api/v1/events/'.$event->id.'/cancel', [
            'reason' => 'Захід скасовано',
            'cancel_public' => true,
        ], $this->headers($user))
            ->assertOk()
            ->assertJsonPath('data.status', 'cancelled');

        $this->assertDatabaseHas('event_histories', [
            'event_id' => $event->id,
            'action' => 'rescheduled',
        ]);
        $this->assertDatabaseHas('event_histories', [
            'event_id' => $event->id,
            'action' => 'cancelled',
        ]);
    }

    public function test_existing_token_loses_access_when_user_role_is_revoked(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $token = $user->createToken('Phone')->plainTextToken;
        $user->update(['role' => 'user']);

        $this->getJson('/api/v1/events', [
            'Authorization' => 'Bearer '.$token,
        ])->assertForbidden();
    }

    private function createEvent(User $user, array $attributes = []): Event
    {
        return Event::query()->create(array_merge([
            'title' => 'Тестова подія',
            'description' => 'Опис події',
            'event_date' => now()->addDay(),
            'status' => EventStatus::Draft->value,
            'has_registration_button' => false,
            'registration_type' => 'none',
            'show_available_slots' => true,
            'user_id' => $user->id,
        ], $attributes));
    }

    private function headers(User $user): array
    {
        return [
            'Authorization' => 'Bearer '.$user->createToken('Test phone')->plainTextToken,
        ];
    }
}
