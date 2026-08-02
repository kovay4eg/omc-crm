<?php

namespace Tests\Feature\Api\V1;

use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\EventSummary;
use App\Models\Registration;
use App\Models\SiteVisit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_dashboard_requires_authentication(): void
    {
        $this->getJson('/api/v1/dashboard')->assertUnauthorized();
    }

    public function test_all_crm_roles_can_open_dashboard(): void
    {
        foreach (['admin', 'editor', 'content'] as $role) {
            $user = User::factory()->create(['role' => $role]);
            $token = $user->createToken($role.' phone')->plainTextToken;

            $this->getJson('/api/v1/dashboard', [
                'Authorization' => 'Bearer '.$token,
            ])->assertOk();
        }
    }

    public function test_dashboard_returns_statistics_and_only_upcoming_active_events(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-31 12:00:00', 'Europe/Kyiv'));

        $user = User::factory()->create(['role' => 'admin']);

        $publishedEvent = $this->createEvent($user, [
            'title' => 'Найближча подія',
            'event_date' => now('Europe/Kyiv')->addDay(),
            'status' => EventStatus::Published->value,
            'has_registration_button' => true,
            'max_participants' => 3,
        ]);
        $this->createEvent($user, [
            'title' => 'Перенесена подія',
            'event_date' => now('Europe/Kyiv')->addDays(2),
            'status' => EventStatus::Rescheduled->value,
        ]);
        $this->createEvent($user, [
            'title' => 'Чернетка',
            'event_date' => now('Europe/Kyiv')->addDays(3),
            'status' => EventStatus::Draft->value,
        ]);
        $this->createEvent($user, [
            'title' => 'Минула подія',
            'event_date' => now('Europe/Kyiv')->subDay(),
            'status' => EventStatus::Published->value,
        ]);

        Registration::query()->create([
            'event_id' => $publishedEvent->id,
            'name' => 'Учасник',
            'email' => 'participant@example.com',
        ]);
        EventSummary::query()->create([
            'event_id' => $publishedEvent->id,
            'status' => EventSummary::STATUS_PUBLISHED,
            'user_id' => $user->id,
        ]);
        SiteVisit::query()->create([
            'session_hash' => str_repeat('a', 64),
            'visited_on' => now('Europe/Kyiv')->toDateString(),
            'last_seen_at' => now('Europe/Kyiv')->subMinute(),
        ]);

        $token = $user->createToken('Test phone')->plainTextToken;

        $this->getJson('/api/v1/dashboard', [
            'Authorization' => 'Bearer '.$token,
        ])
            ->assertOk()
            ->assertJsonPath('data.stats.events_total', 4)
            ->assertJsonPath('data.stats.active_events', 2)
            ->assertJsonPath('data.stats.draft_events', 1)
            ->assertJsonPath('data.stats.registrations_total', 1)
            ->assertJsonPath('data.stats.published_summaries', 1)
            ->assertJsonPath('data.stats.visitors_today', 1)
            ->assertJsonPath('data.stats.online_now', 1)
            ->assertJsonCount(2, 'data.upcoming_events')
            ->assertJsonPath('data.upcoming_events.0.title', 'Найближча подія')
            ->assertJsonPath('data.upcoming_events.0.available_slots', 2)
            ->assertJsonMissing(['title' => 'Чернетка'])
            ->assertJsonMissing(['title' => 'Минула подія']);
    }

    private function createEvent(User $user, array $attributes): Event
    {
        return Event::query()->create(array_merge([
            'title' => 'Подія',
            'description' => 'Опис події',
            'event_date' => now('Europe/Kyiv')->addDay(),
            'status' => EventStatus::Draft->value,
            'has_registration_button' => false,
            'registration_type' => 'none',
            'show_available_slots' => true,
            'user_id' => $user->id,
        ], $attributes));
    }
}
