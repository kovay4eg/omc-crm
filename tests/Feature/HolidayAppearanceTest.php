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
            'saint nicholas day' => ['2027-12-06 12:00:00', 'st-nicholas'],
            'valentines day' => ['2027-02-14 12:00:00', 'valentines'],
            'first day of spring' => ['2027-03-01 12:00:00', 'first-day-spring'],
            'first day of summer' => ['2027-06-01 12:00:00', 'first-day-summer'],
            'first day of autumn' => ['2027-09-01 12:00:00', 'first-day-autumn'],
            'first day of winter' => ['2027-12-01 12:00:00', 'first-day-winter'],
        ];
    }

    #[DataProvider('fixedHolidayBoundaries')]
    public function test_fixed_holiday_logos_switch_on_and_off_at_midnight_in_kyiv(
        string $moment,
        ?string $expectedTheme,
    ): void {
        $theme = HolidayTheme::current(CarbonImmutable::parse($moment, 'Europe/Kyiv'));

        $this->assertSame($expectedTheme, $theme['key'] ?? null);
    }

    public static function fixedHolidayBoundaries(): array
    {
        return [
            'valentines before' => ['2027-02-13 23:59:59', null],
            'valentines starts' => ['2027-02-14 00:00:00', 'valentines'],
            'valentines ends' => ['2027-02-15 00:00:00', null],
            'spring before' => ['2027-02-28 23:59:59', null],
            'spring starts' => ['2027-03-01 00:00:00', 'first-day-spring'],
            'spring ends' => ['2027-03-02 00:00:00', null],
            'summer before' => ['2027-05-31 23:59:59', null],
            'summer starts' => ['2027-06-01 00:00:00', 'first-day-summer'],
            'summer ends' => ['2027-06-02 00:00:00', null],
            'autumn before' => ['2027-08-31 23:59:59', null],
            'autumn starts' => ['2027-09-01 00:00:00', 'first-day-autumn'],
            'autumn ends' => ['2027-09-02 00:00:00', null],
            'winter before' => ['2027-11-30 23:59:59', null],
            'winter starts' => ['2027-12-01 00:00:00', 'first-day-winter'],
            'winter ends' => ['2027-12-02 00:00:00', null],
            'saint nicholas before' => ['2027-12-05 23:59:59', null],
            'saint nicholas starts' => ['2027-12-06 00:00:00', 'st-nicholas'],
            'saint nicholas ends' => ['2027-12-07 00:00:00', null],
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

        foreach ([
            'new-year',
            'christmas',
            'easter',
            'vyshyvanka',
            'independence',
            'youth',
            'st-nicholas',
            'valentines',
            'first-day-spring',
            'first-day-summer',
            'first-day-autumn',
            'first-day-winter',
        ] as $theme) {
            $response->assertSee("images/holiday/logos/{$theme}.svg", false);
            $response->assertSee("holiday-preview={$theme}", false);
            $response->assertSee('logo-version=', false);
        }
    }

    #[DataProvider('holidayPreviewThemes')]
    public function test_each_holiday_preview_uses_its_own_svg(string $theme, string $source): void
    {
        $response = $this->get("/?holiday-preview={$theme}")
            ->assertOk()
            ->assertSee("data-holiday-theme=\"{$theme}\"", false)
            ->assertSee("images/holiday/logos/{$theme}.svg?v=", false);

        $markup = file_get_contents(public_path("images/holiday/logos/{$theme}.svg"));

        $this->assertIsString($markup);
        $this->assertStringContainsString("data-holiday-logo=\"{$theme}\"", $markup);
        $this->assertStringContainsString("data-source=\"{$source}\"", $markup);
        $this->assertMatchesRegularExpression('/<svg\b[^>]*\bviewBox="[^"]+"/i', $markup);
        $this->assertMatchesRegularExpression('/<(?:path|polygon|rect)\b/i', $markup);

        foreach (['<image', 'data:image', 'base64', '<text', '<foreignObject', 'href="http'] as $forbidden) {
            $this->assertStringNotContainsString($forbidden, $markup, "{$theme} contains {$forbidden}");
        }
    }

    public static function holidayPreviewThemes(): array
    {
        return [
            'new year' => ['new-year', 'approved-reference-board'],
            'christmas' => ['christmas', 'approved-reference-board'],
            'easter' => ['easter', 'approved-reference-board'],
            'vyshyvanka' => ['vyshyvanka', 'approved-reference-board'],
            'independence' => ['independence', 'approved-reference-board'],
            'youth' => ['youth', 'approved-reference-board'],
            'saint nicholas' => ['st-nicholas', 'canonical-omc-vector'],
            'valentines' => ['valentines', 'canonical-omc-vector'],
            'first day of spring' => ['first-day-spring', 'canonical-omc-vector'],
            'first day of summer' => ['first-day-summer', 'canonical-omc-vector'],
            'first day of autumn' => ['first-day-autumn', 'canonical-omc-vector'],
            'first day of winter' => ['first-day-winter', 'canonical-omc-vector'],
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
