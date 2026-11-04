<?php

namespace Tests\Feature;

use App\Filament\Widgets\HolidayAppearanceControl;
use App\Models\AdminProAssignment;
use App\Models\SiteSetting;
use App\Models\User;
use App\Support\HolidayTheme;
use Carbon\CarbonImmutable;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class HolidayAppearanceTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('holidayDates')]
    public function test_holiday_dates_select_expected_theme(string $date, string $theme): void
    {
        $this->assertSame($theme, HolidayTheme::current(CarbonImmutable::parse($date, 'Europe/Kyiv'))['key']);
    }

    public static function holidayDates(): array
    {
        return [
            'new year' => ['2027-01-01 12:00:00', 'new-year'],
            'christmas' => ['2027-12-25 12:00:00', 'christmas'],
            'orthodox easter' => ['2027-05-02 12:00:00', 'easter'],
            'vyshyvanka' => ['2027-05-20 12:00:00', 'vyshyvanka'],
            'independence' => ['2027-08-24 12:00:00', 'independence'],
            'youth day' => ['2027-08-12 12:00:00', 'youth'],
        ];
    }

    public function test_holiday_logo_and_winter_decor_are_rendered_only_when_enabled(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2027-12-25 12:00:00', 'Europe/Kyiv'));

        try {
            SiteSetting::query()->create([
                'holiday_logos_enabled' => true,
                'winter_decor_enabled' => true,
            ]);

            $this->get('/')
                ->assertOk()
                ->assertSee('data-holiday-theme="christmas"', false)
                ->assertSee('images/holiday/logos/christmas.svg', false)
                ->assertDontSee('images/holiday/logos/christmas.png', false)
                ->assertSee('Полтавський обласний молодіжний центр вітає вас з Різдвом Христовим!')
                ->assertSee('id="omcSnowfall"', false)
                ->assertSee('omc-winter-garland', false)
                ->assertSee('position: absolute', false)
                ->assertSee('--omc-winter-reserve', false)
                ->assertSee('id="omcHeaderClearance"', false)
                ->assertSee('syncHeaderGeometry', false)
                ->assertSee('initializeGarlandClearance', false)
                ->assertSee('omc-winter-twinkle', false)
                ->assertSee('ResizeObserver', false);

            SiteSetting::query()->update([
                'holiday_logos_enabled' => false,
                'winter_decor_enabled' => false,
            ]);

            $this->get('/')
                ->assertOk()
                ->assertDontSee('data-holiday-theme="christmas"', false)
                ->assertDontSee('id="omcSnowfall"', false);
        } finally {
            CarbonImmutable::setTestNow();
        }
    }

    public function test_local_holiday_logo_preview_lists_all_svg_themes(): void
    {
        $response = $this->get('/holiday-logo-preview')->assertOk();

        foreach (['new-year', 'christmas', 'easter', 'vyshyvanka', 'independence', 'youth'] as $theme) {
            $response->assertSee("images/holiday/logos/{$theme}.svg", false);
            $response->assertSee("holiday-preview={$theme}", false);
            $response->assertSee('logo-version=', false);
        }
    }

    #[DataProvider('holidayPreviewThemes')]
    public function test_each_holiday_preview_uses_its_own_svg(string $theme): void
    {
        $response = $this->get("/?holiday-preview={$theme}")
            ->assertOk()
            ->assertSee("data-holiday-theme=\"{$theme}\"", false)
            ->assertSee("images/holiday/logos/{$theme}.svg?v=", false);

        $markup = file_get_contents(public_path("images/holiday/logos/{$theme}.svg"));

        $this->assertIsString($markup);
        $this->assertStringContainsString("data-holiday-logo=\"{$theme}\"", $markup);
        $this->assertStringContainsString('data-source="approved-reference-board"', $markup);
        $this->assertMatchesRegularExpression('/<svg\b[^>]*\bviewBox="[^"]+"/i', $markup);
        $this->assertMatchesRegularExpression('/<(?:path|polygon|rect)\b/i', $markup);

        foreach (['<image', 'data:image', 'base64', '<text', '<foreignObject', 'href="http'] as $forbidden) {
            $this->assertStringNotContainsString($forbidden, $markup, "{$theme} contains {$forbidden}");
        }
    }

    public static function holidayPreviewThemes(): array
    {
        return [
            'new year' => ['new-year'],
            'christmas' => ['christmas'],
            'easter' => ['easter'],
            'vyshyvanka' => ['vyshyvanka'],
            'independence' => ['independence'],
            'youth' => ['youth'],
        ];
    }

    public function test_only_admin_pro_can_view_and_use_holiday_controls(): void
    {
        $adminPro = User::factory()->create(['role' => 'admin']);
        AdminProAssignment::transferTo($adminPro);
        $this->actingAs($adminPro);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $this->assertTrue(HolidayAppearanceControl::canView());

        Livewire::test(HolidayAppearanceControl::class)
            ->call('toggleHolidayLogos')
            ->assertSet('holidayLogosEnabled', true)
            ->call('toggleWinterDecor')
            ->assertSet('winterDecorEnabled', true);

        $this->assertDatabaseHas('site_settings', [
            'holiday_logos_enabled' => true,
            'winter_decor_enabled' => true,
        ]);
        $this->assertDatabaseHas('system_logs', ['action' => 'toggle_holiday_logos']);
        $this->assertDatabaseHas('system_logs', ['action' => 'toggle_winter_decor']);

        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $this->assertFalse(HolidayAppearanceControl::canView());
    }
}
