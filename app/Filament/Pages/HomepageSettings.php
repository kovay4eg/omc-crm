<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use App\Models\HomepageSetting;

class HomepageSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationLabel = 'Головна сторінка';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-home';

    protected string $view = 'filament.pages.homepage-settings';

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
        $settings = HomepageSetting::first();

        if ($settings) {
            $this->form->fill($settings->toArray());
        }
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([

                FileUpload::make('banner_image')
                    ->label('Банер')
                    ->image()
                    ->disk('public')
                    ->directory('homepage')
                    ->visibility('public')
                    ->preserveFilenames()
                    ->required(),

                FileUpload::make('logo')
                    ->label('Логотип')
                    ->image()
                    ->disk('public')
                    ->directory('homepage')
                    ->visibility('public')
                    ->preserveFilenames(),

                Toggle::make('facebook_enabled')->label('Facebook')->live(),

                TextInput::make('facebook_url')
                    ->label('Facebook URL')
                    ->url()
                    ->hidden(fn ($get) => !$get('facebook_enabled')),

                Toggle::make('instagram_enabled')->label('Instagram')->live(),

                TextInput::make('instagram_url')
                    ->label('Instagram URL')
                    ->url()
                    ->hidden(fn ($get) => !$get('instagram_enabled')),

                Toggle::make('telegram_enabled')->label('Telegram')->live(),

                TextInput::make('telegram_url')
                    ->label('Telegram URL')
                    ->url()
                    ->hidden(fn ($get) => !$get('telegram_enabled')),

                Toggle::make('x_enabled')->label('X (Twitter)')->live(),

                TextInput::make('x_url')
                    ->label('X URL')
                    ->url()
                    ->hidden(fn ($get) => !$get('x_enabled')),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState(); 

        $settings = HomepageSetting::first() ?? new HomepageSetting();

        $settings->banner_image = $data['banner_image'] ?? null;
        $settings->logo = $data['logo'] ?? null;

        $settings->facebook_enabled = !empty($data['facebook_enabled']);
        $settings->facebook_url = $data['facebook_url'] ?? null;

        $settings->instagram_enabled = !empty($data['instagram_enabled']);
        $settings->instagram_url = $data['instagram_url'] ?? null;

        $settings->telegram_enabled = !empty($data['telegram_enabled']);
        $settings->telegram_url = $data['telegram_url'] ?? null;

        $settings->x_enabled = !empty($data['x_enabled']);
        $settings->x_url = $data['x_url'] ?? null;

        $settings->save();

        $this->form->fill($settings->toArray()); 
    }
}