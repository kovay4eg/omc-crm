<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class Weather extends Widget
{
    protected string $view = 'filament.widgets.weather';

    protected int|string|array $columnSpan = 1;

    protected function getExtraAttributes(): array
    {
        return [
            'class' => 'self-start',
        ];
    }
}