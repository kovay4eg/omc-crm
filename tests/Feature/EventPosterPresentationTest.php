<?php

namespace Tests\Feature;

use App\Enums\EventStatus;
use App\Filament\Resources\Events\Schemas\EventForm;
use App\Models\Event;
use App\Models\EventSummary;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\FormsComponent;
use Filament\Schemas\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventPosterPresentationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_event_and_summary_cards_render_posters_at_four_by_five(): void
    {
        Event::query()->create([
            'title' => 'Майбутній захід',
            'description' => 'Анонс для перевірки формату афіші.',
            'event_date' => now()->addDay(),
            'status' => EventStatus::Published->value,
            'has_registration_button' => false,
            'registration_type' => 'none',
            'show_available_slots' => true,
        ]);

        $pastEvent = Event::query()->create([
            'title' => 'Минулий захід',
            'description' => 'Підсумок для перевірки формату афіші.',
            'event_date' => now()->subDay(),
            'status' => EventStatus::Published->value,
            'has_registration_button' => false,
            'registration_type' => 'none',
            'show_available_slots' => true,
        ]);

        EventSummary::query()->create([
            'event_id' => $pastEvent->getKey(),
            'summary' => 'Захід відбувся.',
            'status' => EventSummary::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        $html = $this->get('/')
            ->assertOk()
            ->assertSee('<div class="event-image">', false)
            ->assertSee('<div class="event-summary-card-poster">', false)
            ->getContent();

        $this->assertMatchesRegularExpression(
            '/\.event-image\s*\{[^}]*aspect-ratio:\s*4\s*\/\s*5\s*;/s',
            $html,
        );
        $this->assertMatchesRegularExpression(
            '/\.event-card-body\s*\{[^}]*padding:\s*calc\(125%\s*\+\s*17px\)\s+17px\s+15px\s*;/s',
            $html,
        );
        $this->assertMatchesRegularExpression(
            '/\.event-summary-card-poster\s*\{[^}]*aspect-ratio:\s*4\s*\/\s*5\s*;/s',
            $html,
        );
        $this->assertMatchesRegularExpression(
            '/\.event-image\s+img\s*\{[^}]*object-fit:\s*cover\s*;/s',
            $html,
        );
        $this->assertMatchesRegularExpression(
            '/\.event-summary-card-poster\s+img\s*\{[^}]*object-fit:\s*cover\s*;/s',
            $html,
        );
    }

    public function test_event_form_forces_the_main_poster_to_four_by_five(): void
    {
        $schema = EventForm::schema(Schema::make(new class extends FormsComponent {}));

        $imageUpload = collect($schema->getFlatComponents(withHidden: true))
            ->first(fn (mixed $component): bool => $component instanceof FileUpload && $component->getName() === 'image');

        $this->assertInstanceOf(FileUpload::class, $imageUpload);
        $this->assertTrue($imageUpload->hasImageEditor());
        $this->assertTrue($imageUpload->shouldAutomaticallyOpenImageEditorForAspectRatio());
        $this->assertSame('4:5', $imageUpload->getImageAspectRatio());
        $this->assertSame('4:5', $imageUpload->getAutomaticallyCropImagesAspectRatio());
        $this->assertSame('4:5', $imageUpload->getPanelAspectRatio());
    }

    public function test_detail_posters_stay_nine_by_sixteen_without_stretching_to_long_content(): void
    {
        $event = Event::query()->create([
            'title' => 'Детальна сторінка анонсу',
            'description' => str_repeat('Довгий опис заходу. ', 80),
            'event_date' => now()->addDay(),
            'status' => EventStatus::Published->value,
            'has_registration_button' => false,
            'registration_type' => 'none',
            'show_available_slots' => true,
        ]);

        $pastEvent = Event::query()->create([
            'title' => 'Детальна сторінка підсумку',
            'description' => str_repeat('Довгий опис підсумку. ', 80),
            'event_date' => now()->subDay(),
            'status' => EventStatus::Published->value,
            'has_registration_button' => false,
            'registration_type' => 'none',
            'show_available_slots' => true,
        ]);

        $summary = EventSummary::query()->create([
            'event_id' => $pastEvent->getKey(),
            'summary' => str_repeat('Розгорнутий текст про результати заходу. ', 80),
            'status' => EventSummary::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        $eventHtml = $this->get(route('events.show', ['event' => $event]))
            ->assertOk()
            ->assertSee('<div class="event-detail-poster">', false)
            ->getContent();

        $this->assertMatchesRegularExpression(
            '/\.event-detail-poster\s*\{[^}]*align-self:\s*start\s*;[^}]*aspect-ratio:\s*9\s*\/\s*16\s*;[^}]*overflow:\s*hidden\s*;/s',
            $eventHtml,
        );
        $this->assertMatchesRegularExpression(
            '/\.event-detail-poster\s+img\s*\{[^}]*object-fit:\s*cover\s*;/s',
            $eventHtml,
        );
        $this->assertSame(1, substr_count($eventHtml, '.event-detail-poster {'));
        $this->assertDoesNotMatchRegularExpression(
            '/\.event-detail-poster[^{]*\{[^}]*min-height\s*:/s',
            $eventHtml,
        );
        $this->assertDoesNotMatchRegularExpression(
            '/\.event-detail-placeholder\s*\{[^}]*min-height\s*:/s',
            $eventHtml,
        );
        $this->assertStringContainsString('id="eventDetailWatermarkTrack"', $eventHtml);
        $this->assertStringContainsString('АНОНСИ ЗАХОДІВ', $eventHtml);
        $this->assertStringContainsString("window.matchMedia('(prefers-reduced-motion: reduce)')", $eventHtml);
        $this->assertStringContainsString('scrollInsidePage * 0.28', $eventHtml);
        $this->assertStringContainsString('translate3d(calc(-50% - ${offset}px), -50%, 0)', $eventHtml);
        $this->assertMatchesRegularExpression(
            '/\.event-detail-watermark-track\s*\{[^}]*width:\s*max-content\s*;[^}]*will-change:\s*transform\s*;/s',
            $eventHtml,
        );

        $summaryHtml = $this->get(route('event-summaries.show', ['eventSummary' => $summary]))
            ->assertOk()
            ->assertSee('<div class="summary-event-poster">', false)
            ->getContent();

        $this->assertMatchesRegularExpression(
            '/\.summary-event-poster\s*\{[^}]*align-self:\s*start\s*;[^}]*aspect-ratio:\s*9\s*\/\s*16\s*;[^}]*overflow:\s*hidden\s*;/s',
            $summaryHtml,
        );
        $this->assertMatchesRegularExpression(
            '/\.summary-event-poster\s+img\s*\{[^}]*object-fit:\s*cover\s*;/s',
            $summaryHtml,
        );
        $this->assertSame(1, substr_count($summaryHtml, '.summary-event-poster {'));
        $this->assertDoesNotMatchRegularExpression(
            '/\.summary-event-poster[^{]*\{[^}]*min-height\s*:/s',
            $summaryHtml,
        );
        $this->assertDoesNotMatchRegularExpression(
            '/\.summary-event-placeholder\s*\{[^}]*min-height\s*:/s',
            $summaryHtml,
        );
        $this->assertMatchesRegularExpression(
            '/\.summary-gallery-item\s*\{[^}]*aspect-ratio:\s*1\s*\/\s*\.72\s*;/s',
            $summaryHtml,
        );
    }
}
