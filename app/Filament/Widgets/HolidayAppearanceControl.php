<?php

namespace App\Filament\Widgets;

use App\Models\SiteSetting;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class HolidayAppearanceControl extends Widget
{
    protected string $view = 'filament.widgets.holiday-appearance-control';

    protected int|string|array $columnSpan = 'full';

    public bool $holidayLogosEnabled = false;

    public bool $winterDecorEnabled = false;

    public static function canView(): bool
    {
        return Auth::user()?->isAdminPro() ?? false;
    }

    public function mount(): void
    {
        $settings = SiteSetting::query()->first();
        $this->holidayLogosEnabled = (bool) $settings?->holiday_logos_enabled;
        $this->winterDecorEnabled = (bool) $settings?->winter_decor_enabled;
    }

    public function toggleHolidayLogos(): void
    {
        $this->authorizeAdminPro();
        $settings = SiteSetting::query()->firstOrCreate([]);
        $settings->holiday_logos_enabled = ! $settings->holiday_logos_enabled;
        $settings->save();
        $this->holidayLogosEnabled = $settings->holiday_logos_enabled;

        system_log(
            'toggle_holiday_logos',
            $this->holidayLogosEnabled ? 'Увімкнено автоматичні святкові логотипи.' : 'Вимкнено автоматичні святкові логотипи.',
        );

        $this->notify('Святкові логотипи', $this->holidayLogosEnabled);
    }

    public function toggleWinterDecor(): void
    {
        $this->authorizeAdminPro();
        $settings = SiteSetting::query()->firstOrCreate([]);
        $settings->winter_decor_enabled = ! $settings->winter_decor_enabled;
        $settings->save();
        $this->winterDecorEnabled = $settings->winter_decor_enabled;

        system_log(
            'toggle_winter_decor',
            $this->winterDecorEnabled ? 'Увімкнено святкову гірлянду та снігопад.' : 'Вимкнено святкову гірлянду та снігопад.',
        );

        $this->notify('Гірлянда та снігопад', $this->winterDecorEnabled);
    }

    private function authorizeAdminPro(): void
    {
        abort_unless(Auth::user()?->isAdminPro(), 403);
    }

    private function notify(string $feature, bool $enabled): void
    {
        Notification::make()
            ->title($feature.' '.($enabled ? 'увімкнено' : 'вимкнено'))
            ->color($enabled ? 'success' : 'gray')
            ->send();
    }
}
