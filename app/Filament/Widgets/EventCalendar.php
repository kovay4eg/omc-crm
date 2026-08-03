<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class EventCalendar extends Widget
{
    protected string $view = 'filament.widgets.event-calendar';

    protected int|string|array $columnSpan = 'full';
}
