<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class HelpGuide extends Page
{
    protected static ?string $navigationLabel = 'Довідка';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static string | \UnitEnum | null $navigationGroup = 'Допомога';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.help-guide';

    public static function canAccess(): bool
    {
        return in_array(auth()->user()?->getActiveRole(), ['admin', 'editor', 'content']);
    }
}
