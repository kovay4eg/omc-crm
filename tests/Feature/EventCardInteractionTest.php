<?php

namespace Tests\Feature;

use App\Enums\EventStatus;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventCardInteractionTest extends TestCase
{
    use RefreshDatabase;

    public function test_entire_event_card_opens_details_without_blocking_registration_controls(): void
    {
        $event = Event::query()->create([
            'title' => 'Клікабельний анонс',
            'description' => 'Перевірка переходу з усієї картки.',
            'event_date' => now()->addDay(),
            'status' => EventStatus::Published->value,
            'has_registration_button' => true,
            'registration_type' => 'internal',
            'show_available_slots' => true,
        ]);

        $html = $this->get('/')
            ->assertOk()
            ->assertSee('class="event-card-overlay-link"', false)
            ->assertSee('href="'.route('events.show', ['event' => $event]).'"', false)
            ->assertSee('aria-label="Детальніше про захід: Клікабельний анонс"', false)
            ->assertSee('data-registration-open', false)
            ->getContent();

        $this->assertMatchesRegularExpression(
            '/\.event-card-overlay-link\s*\{[^}]*inset:\s*0\s*;[^}]*z-index:\s*2\s*;/s',
            $html,
        );
        $this->assertMatchesRegularExpression(
            '/\.event-card-overlay-link:focus-visible\s*\{[^}]*outline:\s*3px\s+solid\s+var\(--events-blue\)\s*;/s',
            $html,
        );
        $this->assertMatchesRegularExpression(
            '/\.event-card-body\s*\{[^}]*z-index:\s*3\s*;[^}]*pointer-events:\s*none\s*;/s',
            $html,
        );
        $this->assertMatchesRegularExpression(
            '/\.event-card-footer\s*\{[^}]*z-index:\s*3\s*;[^}]*pointer-events:\s*none\s*;/s',
            $html,
        );
        $this->assertMatchesRegularExpression(
            '/\.event-card-footer\s*>\s*\*\s*\{[^}]*pointer-events:\s*auto\s*;/s',
            $html,
        );
    }
}
