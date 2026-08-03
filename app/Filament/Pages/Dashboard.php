<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\Clock;
use App\Filament\Widgets\EventCalendar;
use App\Filament\Widgets\MaintenanceModeControl;
use App\Filament\Widgets\StatsOverview;
use App\Filament\Widgets\Weather;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\Auth;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Адмін-панель';

    protected static ?string $navigationLabel = 'Адмін-панель';

    protected function getHeaderActions(): array
    {
        if (! Auth::user()?->isAdmin()) {
            return [];
        }

        return [
            Action::make('preview_role')
                ->label('Переглядати як')
                ->form([
                    Select::make('role')
                        ->label('Роль')
                        ->options([
                            'admin' => '👑 Адмін',
                            'editor' => '🛠 Редактор',
                            'content' => '🎨 Контент-мейкер',
                        ])
                        ->default(session('preview_role', 'admin'))
                        ->required(),
                ])
                ->action(function (array $data) {
                    session(['preview_role' => $data['role']]);

                    return redirect()->route('filament.admin.pages.dashboard');
                })
                ->modalSubmitActionLabel('Застосувати'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            MaintenanceModeControl::class,
            Clock::class,
            Weather::class,
            StatsOverview::class,
            EventCalendar::class,
        ];
    }

    public function getColumns(): int|array
    {
        return 2;
    }

    public function getWidgets(): array
    {
        return [];
    }
}
