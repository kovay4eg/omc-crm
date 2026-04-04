<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Адмін-панель';

    protected static ?string $navigationLabel = 'Адмін-панель';

    protected function getHeaderActions(): array
    {
        if (!Auth::user()?->isAdmin()) {
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
            \App\Filament\Widgets\Clock::class,
            \App\Filament\Widgets\Weather::class,
            \App\Filament\Widgets\StatsOverview::class,
            \App\Filament\Widgets\EventCalendar::class,
        ];
    }
    public function getColumns(): int | array
    {
        return 2;
    } 
    public function getWidgets(): array
    {
        return [];
    }
}