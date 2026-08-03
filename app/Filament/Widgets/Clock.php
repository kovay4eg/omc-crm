<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class Clock extends Widget
{
    protected string $view = 'filament.widgets.clock';

    protected int|string|array $columnSpan = 1;
}
