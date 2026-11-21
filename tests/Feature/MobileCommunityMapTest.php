<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileCommunityMapTest extends TestCase
{
    use RefreshDatabase;

    public function test_mobile_work_panel_places_the_svg_map_before_the_description_and_photos(): void
    {
        $html = $this->get('/')
            ->assertOk()
            ->assertSee('data-mobile-community-map', false)
            ->assertSee('data-mobile-community-map-title', false)
            ->assertSee('aria-label="Де ми були у 2026 році?"', false)
            ->assertSee('Де ми були у 2026 році?', false)
            ->assertSee('aria-labelledby="mobile-community-map-title"', false)
            ->assertSee('data-animation="points"', false)
            ->assertSee('images/structure/mobile/community-map.svg', false)
            ->getContent();

        $mobileStart = strpos($html, 'id="mobile-view"');
        $careerStart = strpos($html, 'id="career-view"');

        $this->assertNotFalse($mobileStart);
        $this->assertNotFalse($careerStart);

        $mobileMarkup = substr($html, $mobileStart, $careerStart - $mobileStart);
        $mapPosition = strpos($mobileMarkup, 'data-mobile-community-map');
        $titlePosition = strpos($mobileMarkup, 'data-mobile-community-map-title');
        $stagePosition = strpos($mobileMarkup, 'data-mobile-community-map-stage');
        $descriptionPosition = strpos($mobileMarkup, 'Не чекаємо, поки ти доїдеш до Полтави');
        $photosPosition = strpos($mobileMarkup, 'data-gallery-key="mobile"');

        $this->assertNotFalse($mapPosition);
        $this->assertNotFalse($titlePosition);
        $this->assertNotFalse($stagePosition);
        $this->assertNotFalse($descriptionPosition);
        $this->assertNotFalse($photosPosition);
        $this->assertLessThan($stagePosition, $titlePosition);
        $this->assertLessThan($descriptionPosition, $mapPosition);
        $this->assertLessThan($photosPosition, $descriptionPosition);
        $this->assertSame(26, substr_count($mobileMarkup, 'class="structure-carousel__slide"'));
        $this->assertStringNotContainsString('class="red-block"', $mobileMarkup);
    }

    public function test_map_uses_only_the_selected_points_animation_and_accessible_community_names(): void
    {
        $response = $this->get('/')->assertOk();

        $response->assertSee('data-animation="points"', false);
        $response->assertDontSee('data-map-animation-option', false);
        $response->assertDontSee('mobile-community-map__preview-controls', false);
        $response->assertDontSee('mobile-community-map__preview-hint', false);

        foreach ([
            'Пирятинська',
            'Лохвицька',
            'Котелевська',
            'Опішнянська',
            'Мартинівська',
            'Новогалещинська',
            'Кременчуцька',
            'Горішньоплавнівська',
        ] as $community) {
            $response->assertSee($community, false);
        }
    }

    public function test_supplied_svg_is_local_self_contained_and_has_eight_points_and_labels(): void
    {
        $path = public_path('images/structure/mobile/community-map.svg');

        $this->assertFileExists($path);

        $svg = file_get_contents($path);

        $this->assertStringContainsString('viewBox="0 0 1509.81 1080"', $svg);
        $this->assertSame(8, substr_count($svg, '<circle class="st1"'));
        $this->assertSame(8, substr_count($svg, '<rect class="st0"'));
        $this->assertSame(27, substr_count($svg, '<path class="st2"'));
        $this->assertStringNotContainsString('<script', strtolower($svg));
        $this->assertStringNotContainsString('<foreignobject', strtolower($svg));
        $this->assertDoesNotMatchRegularExpression('/(?:href|xlink:href)\s*=\s*["\'](?:https?:|\/\/|data:)/i', $svg);
    }

    public function test_map_animation_replays_on_every_panel_open_and_touch_has_no_selection_state(): void
    {
        $structureTemplate = file_get_contents(resource_path('views/structure/structure.blade.php'));
        $styles = file_get_contents(public_path('css/mobile-community-map.css'));
        $script = file_get_contents(public_path('js/mobile-community-map.js'));

        $this->assertStringContainsString("new CustomEvent('structure:view-opened'", $structureTemplate);
        $this->assertStringContainsString("document.addEventListener('structure:view-opened'", $script);
        $this->assertStringContainsString("block.classList.remove('is-playing')", $script);
        $this->assertStringContainsString("block.classList.add('is-playing')", $script);
        $this->assertStringNotContainsString("target.addEventListener('click'", $script);
        $this->assertStringNotContainsString("addEventListener('mouseenter'", $script);
        $this->assertStringNotContainsString("window.matchMedia('(hover: hover)", $script);

        $this->assertStringContainsString('touch-action: pan-y pinch-zoom;', $styles);
        $this->assertStringContainsString('pointer-events: none;', $styles);
        $this->assertStringContainsString('@media (prefers-reduced-motion: reduce)', $styles);
        $this->assertStringContainsString('mobile-community-map__ray', $styles);
        $this->assertStringContainsString('@keyframes mobile-community-ray-reveal', $styles);
        $this->assertStringContainsString('@keyframes mobile-community-map-title-sheen', $styles);
        $this->assertStringContainsString('--map-community-start-delay: .55s;', $styles);
        $this->assertStringContainsString('--map-sheen-duration: 4.2s;', $styles);
        $this->assertStringContainsString('var(--map-community-start-delay)', $styles);
        $this->assertStringContainsString('var(--map-sheen-duration)', $styles);
        $this->assertStringContainsString('var(--map-sheen-delay)', $styles);
        $this->assertStringContainsString('-webkit-text-fill-color: currentColor;', $styles);
        $this->assertStringNotContainsString('-webkit-text-fill-color: transparent;', $styles);
        $this->assertStringNotContainsString('background-clip: text;', $styles);
        $this->assertStringNotContainsString('.mobile-community-map__title::after', $styles);
        $this->assertStringNotContainsString('data-sheen-text', $styles);
        $this->assertStringNotContainsString('mobile-community-map-title-enter', $styles);
        $this->assertStringNotContainsString('mobile-community-map-enter', $styles);
        $this->assertStringNotContainsString('.mobile-community-map.is-enhanced .mobile-community-map__motion', $styles);
        $this->assertStringContainsString('.mobile-community-map.is-enhanced .mobile-community-map__point', $styles);
        $this->assertStringContainsString("ray.classList.add('mobile-community-map__ray')", $script);
        $this->assertStringContainsString("ray.style.setProperty('--ray-clip-start'", $script);
        $this->assertStringContainsString('mobile-community-point-idle 4.8s', $styles);
        $this->assertStringContainsString('mobile-community-label-idle 4.8s', $styles);
        $this->assertStringContainsString('@keyframes mobile-community-label-idle', $styles);
        $this->assertStringContainsString('mobile-community-map-sheen var(--map-sheen-duration)', $styles);
        $this->assertStringContainsString('mobile-community-map-title-sheen var(--map-sheen-duration)', $styles);
        $this->assertStringContainsString('color: var(--map-violet-active);', $styles);
        $this->assertStringContainsString('text-shadow: 0 0 12px rgba(102, 112, 238, .32);', $styles);
        $this->assertStringContainsString('?v=20261117-10', file_get_contents(resource_path('views/structure/mobile-community-map.blade.php')));
        $this->assertStringNotContainsString('?v=20261117-9', file_get_contents(resource_path('views/structure/mobile-community-map.blade.php')));
        $this->assertStringNotContainsString('data-sheen-text', file_get_contents(resource_path('views/structure/mobile-community-map.blade.php')));
        $this->assertStringContainsString('applyAnimationTiming(part, community.order)', $script);
        $this->assertStringNotContainsString('var(--label-order) *', $styles);
        $this->assertStringNotContainsString('data-map-animation-option', $script);
        $this->assertStringNotContainsString('localStorage', $script);
        $this->assertStringNotContainsString('animationNames', $script);
        $this->assertStringNotContainsString('[data-animation="calm"]', $styles);
        $this->assertStringNotContainsString('[data-animation="light"]', $styles);
        $this->assertStringNotContainsString('mobile-community-label-wave', $styles);
        $this->assertStringNotContainsString('mobile-community-map__preview-', $styles);
    }
}
