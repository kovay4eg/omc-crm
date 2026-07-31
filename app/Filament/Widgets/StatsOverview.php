<?php

namespace App\Filament\Widgets;

use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\SiteVisit;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $today = now('Europe/Kyiv')->toDateString();
        $onlineSince = now('Europe/Kyiv')->subMinutes(5);

        return [
            Stat::make('Всього заходів', Event::count())
                ->description('Усі записи')
                ->icon('heroicon-o-calendar')
                ->color('primary'),

            Stat::make('Актуальні заходи', Event::query()
                ->whereDate('event_date', '>=', $today)
                ->whereIn('status', [EventStatus::Published->value, EventStatus::Rescheduled->value])
                ->count())
                ->description('Опубліковані або перенесені')
                ->icon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('Чернетки заходів', Event::where('status', EventStatus::Draft->value)->count())
                ->description('Не опубліковані')
                ->icon('heroicon-o-pencil-square')
                ->color('warning'),

            Stat::make('Користувачі адмін-панелі', User::count())
                ->description('Облікові записи в системі')
                ->icon('heroicon-o-users')
                ->color('info'),

            Stat::make('Відвідувачі сьогодні', SiteVisit::whereDate('visited_on', $today)->count())
                ->description('Унікальні сесії за день')
                ->icon('heroicon-o-chart-bar')
                ->color('success'),

            Stat::make('Онлайн зараз', SiteVisit::where('last_seen_at', '>=', $onlineSince)->count())
                ->description('Активність за останні 5 хв.')
                ->icon('heroicon-o-signal')
                ->color('primary'),
        ];
    }
}
