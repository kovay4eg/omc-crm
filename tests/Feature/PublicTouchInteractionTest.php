<?php

namespace Tests\Feature;

use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\EventSummary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicTouchInteractionTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_disable_double_tap_zoom_without_blocking_accessible_zoom(): void
    {
        $event = Event::query()->create([
            'title' => 'Майбутній захід',
            'description' => 'Опис майбутнього заходу.',
            'event_date' => now()->addDay(),
            'status' => EventStatus::Published->value,
            'has_registration_button' => false,
            'registration_type' => 'none',
            'show_available_slots' => true,
        ]);

        $pastEvent = Event::query()->create([
            'title' => 'Минулий захід',
            'description' => 'Опис минулого заходу.',
            'event_date' => now()->subDay(),
            'status' => EventStatus::Published->value,
            'has_registration_button' => false,
            'registration_type' => 'none',
            'show_available_slots' => true,
        ]);

        $summary = EventSummary::query()->create([
            'event_id' => $pastEvent->getKey(),
            'summary' => 'Підсумок минулого заходу.',
            'status' => EventSummary::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        $responses = [
            $this->get('/'),
            $this->get(route('events.show', ['event' => $event])),
            $this->get(route('event-summaries.show', ['eventSummary' => $summary])),
        ];

        foreach ($responses as $response) {
            $html = $response
                ->assertOk()
                ->assertSee('touch-action: manipulation;', false)
                ->getContent();

            $this->assertStringContainsString(
                'name="viewport" content="width=device-width, initial-scale=1.0"',
                $html,
            );
            $this->assertStringNotContainsString('user-scalable=no', strtolower($html));
            $this->assertStringNotContainsString('maximum-scale', strtolower($html));
        }
    }

    public function test_public_carousels_keep_swipe_and_pinch_zoom_gestures(): void
    {
        $teamStyles = file_get_contents(public_path('css/team-departments.css'));
        $structureTemplate = file_get_contents(resource_path('views/structure/structure.blade.php'));

        $this->assertMatchesRegularExpression(
            '/\.department-carousel-stage\s*\{[^}]*touch-action:\s*pan-y pinch-zoom\s*;/s',
            $teamStyles,
        );
        $this->assertMatchesRegularExpression(
            '/\.innovation-gallery__viewport\s*\{[^}]*touch-action:\s*pan-x pan-y pinch-zoom\s*;/s',
            $teamStyles,
        );
        $this->assertMatchesRegularExpression(
            '/\.structure-carousel__viewport\s*\{[^}]*touch-action:\s*pan-x pan-y pinch-zoom\s*;/s',
            $structureTemplate,
        );
    }

    public function test_overlay_carousel_arrows_are_semitransparent_over_photos(): void
    {
        $teamStyles = file_get_contents(public_path('css/team-departments.css'));
        $structureTemplate = file_get_contents(resource_path('views/structure/structure.blade.php'));

        $this->assertMatchesRegularExpression(
            '/\.innovation-gallery__arrow\s*\{[^}]*background:\s*rgba\(43,\s*36,\s*193,\s*\.58\)\s*;/s',
            $teamStyles,
        );
        $this->assertMatchesRegularExpression(
            '/\.innovation-gallery__arrow:hover:not\(:disabled\)\s*\{[^}]*background:\s*rgba\(31,\s*25,\s*158,\s*\.78\)\s*;/s',
            $teamStyles,
        );
        $this->assertMatchesRegularExpression(
            '/\.structure-carousel__arrow\s*\{[^}]*background:\s*rgba\(43,\s*36,\s*193,\s*\.58\)\s*;/s',
            $structureTemplate,
        );
        $this->assertMatchesRegularExpression(
            '/\.structure-carousel__arrow:hover:not\(:disabled\)\s*\{[^}]*background:\s*rgba\(31,\s*25,\s*158,\s*\.78\)\s*;/s',
            $structureTemplate,
        );
    }
}
