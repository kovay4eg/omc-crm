<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Event;
use App\Models\User;

class StatsOverview extends BaseWidget
{
    protected ?string $pollingInterval = '10s';

    protected function getStats(): array
    {
        return [

            Stat::make('Всього івентів', Event::count())
                ->description('Всього')
                ->icon('heroicon-o-calendar')
                ->color('primary'),

            Stat::make('Опубліковані івенти', Event::where('status', 'published')->count())
                ->description('Активні')
                ->icon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('Чернетки івентів', Event::where('status', 'draft')->count())
                ->description('Не опубліковані')
                ->icon('heroicon-o-pencil-square')
                ->color('warning'),

            Stat::make('Користувачі адмін-панелі', User::count())
                ->description('В системі')
                ->icon('heroicon-o-users')
                ->color('info'),

            Stat::make('Відвідувачі сьогодні на сайті', rand(50, 200))
                ->description('Mock')
                ->icon('heroicon-o-chart-bar')
                ->color('success'),

            Stat::make('Онлайн зараз на сайті', rand(1, 15))
                ->description('Mock')
                ->icon('heroicon-o-signal')
                ->color('danger'),
        ];
    }
}