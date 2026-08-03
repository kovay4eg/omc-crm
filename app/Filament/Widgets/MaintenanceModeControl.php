<?php

namespace App\Filament\Widgets;

use App\Models\SiteSetting;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class MaintenanceModeControl extends Widget
{
    protected string $view = 'filament.widgets.maintenance-mode-control';

    protected int|string|array $columnSpan = 'full';

    public bool $maintenanceMode = false;

    public static function canView(): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }

    public function mount(): void
    {
        $this->maintenanceMode = (bool) SiteSetting::query()
            ->value('maintenance_mode');
    }

    public function toggleMaintenanceMode(): void
    {
        abort_unless(Auth::user()?->isAdmin(), 403);

        $settings = SiteSetting::first() ?? new SiteSetting;
        $settings->maintenance_mode = ! $settings->maintenance_mode;
        $settings->save();

        $this->maintenanceMode = $settings->maintenance_mode;

        Notification::make()
            ->title(
                $this->maintenanceMode
                    ? 'Режим технічних робіт увімкнено'
                    : 'Режим технічних робіт вимкнено',
            )
            ->body(
                $this->maintenanceMode
                    ? 'Фронтенд тимчасово недоступний. Адмін-панель продовжує працювати.'
                    : 'Фронтенд знову доступний для відвідувачів.',
            )
            ->color($this->maintenanceMode ? 'danger' : 'success')
            ->send();
    }
}
