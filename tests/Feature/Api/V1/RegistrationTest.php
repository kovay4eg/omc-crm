<?php

namespace Tests\Feature\Api\V1;

use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_crm_user_can_manage_event_registrations(): void
    {
        $user = User::factory()->create(['role' => 'editor']);
        $event = $this->createEvent($user);
        $headers = $this->headers($user);

        $createResponse = $this->postJson('/api/v1/events/'.$event->id.'/registrations', [
            'name' => 'Іван Петренко',
            'email' => 'ivan@example.com',
            'phone' => '+380501112233',
        ], $headers)
            ->assertCreated()
            ->assertJsonPath('data.name', 'Іван Петренко');

        $registrationId = $createResponse->json('data.id');

        $this->getJson('/api/v1/events/'.$event->id.'/registrations?search=ivan', $headers)
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->patchJson('/api/v1/events/'.$event->id.'/registrations/'.$registrationId, [
            'name' => 'Іван Оновлений',
        ], $headers)
            ->assertOk()
            ->assertJsonPath('data.name', 'Іван Оновлений');

        $this->deleteJson('/api/v1/events/'.$event->id.'/registrations/'.$registrationId, [], $headers)
            ->assertNoContent();

        $this->assertDatabaseMissing('registrations', ['id' => $registrationId]);
    }

    public function test_duplicate_email_and_phone_are_rejected_within_event(): void
    {
        $user = User::factory()->create(['role' => 'content']);
        $event = $this->createEvent($user);
        $event->registrations()->create([
            'name' => 'Перший учасник',
            'email' => 'same@example.com',
            'phone' => '+380500000001',
        ]);

        $this->postJson('/api/v1/events/'.$event->id.'/registrations', [
            'name' => 'Другий учасник',
            'email' => 'same@example.com',
            'phone' => '+380500000002',
        ], $this->headers($user))->assertUnprocessable();
    }

    public function test_capacity_and_cancelled_event_are_enforced(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $event = $this->createEvent($user, ['max_participants' => 1]);
        $event->registrations()->create([
            'name' => 'Перший учасник',
            'email' => 'first@example.com',
            'phone' => '+380500000001',
        ]);

        $payload = [
            'name' => 'Другий учасник',
            'email' => 'second@example.com',
            'phone' => '+380500000002',
        ];

        $this->postJson('/api/v1/events/'.$event->id.'/registrations', $payload, $this->headers($user))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('limit');

        $event->update(['status' => EventStatus::Cancelled]);
        $this->postJson('/api/v1/events/'.$event->id.'/registrations', $payload, $this->headers($user))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('event');
    }

    public function test_registration_cannot_be_managed_through_another_event(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $event = $this->createEvent($user);
        $anotherEvent = $this->createEvent($user, ['title' => 'Інша подія']);
        $registration = $event->registrations()->create([
            'name' => 'Учасник',
            'email' => 'participant@example.com',
            'phone' => '+380500000001',
        ]);

        $this->deleteJson(
            '/api/v1/events/'.$anotherEvent->id.'/registrations/'.$registration->id,
            [],
            $this->headers($user),
        )->assertNotFound();

        $this->assertDatabaseHas('registrations', ['id' => $registration->id]);
    }

    private function createEvent(User $user, array $attributes = []): Event
    {
        return Event::query()->create(array_merge([
            'title' => 'Подія',
            'description' => 'Опис',
            'event_date' => now()->addDay(),
            'status' => EventStatus::Published->value,
            'has_registration_button' => true,
            'registration_type' => 'internal',
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
