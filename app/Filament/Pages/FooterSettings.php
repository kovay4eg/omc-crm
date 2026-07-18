<?php

namespace App\Filament\Pages;

use App\Models\FooterSetting;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;

class FooterSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationLabel = 'Футер';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-photo';

    protected string $view = 'filament.pages.footer-settings';

    public ?array $data = [];

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Зберегти')
                ->action('save')
                ->color('primary'),
        ];
    }

    public function mount(): void
    {
        $settings = FooterSetting::first();

        $this->form->fill([
            'partner_logos' => $settings?->partner_logos ?? [],
        ]);
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                FileUpload::make('partner_logos')
                    ->label('Логотипи партнерів або спонсорів')
                    ->image()
                    ->multiple()
                    ->reorderable()
                    ->appendFiles()
                    ->maxFiles(5)
                    ->disk('public')
                    ->directory('footer-partners')
                    ->visibility('public')
                    ->helperText('Необов’язково. Можна додати до 5 логотипів і змінити їх порядок.'),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $settings = FooterSetting::first() ?? new FooterSetting();

        $settings->partner_logos = $this->form->getState()['partner_logos'] ?? [];
        $settings->save();

        $this->form->fill([
            'partner_logos' => $settings->partner_logos ?? [],
        ]);
    }
}
