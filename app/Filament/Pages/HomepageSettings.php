<?php

namespace App\Filament\Pages;

use App\Models\HomepageSetting;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class HomepageSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationLabel = 'Головна сторінка';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-home';

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

        $formData = [
            'contact_address' => 'м. Полтава, просп. Віталія Грицаєнка, 25',
            'contact_phone' => '+380 (095) 580-90-62',
            'contact_email' => 'poltomc@gmail.com',
            'google_maps_url' => 'https://www.google.com/maps?q=%D0%BC.%20%D0%9F%D0%BE%D0%BB%D1%82%D0%B0%D0%B2%D0%B0%2C%20%D0%BF%D1%80%D0%BE%D1%81%D0%BF.%20%D0%92%D1%96%D1%82%D0%B0%D0%BB%D1%96%D1%8F%20%D0%93%D1%80%D0%B8%D1%86%D0%B0%D1%94%D0%BD%D0%BA%D0%B0%2C%2025&output=embed',
        ];

        if ($settings) {
            $formData = array_merge(
                $formData,
                array_filter(
                    $settings->toArray(),
                    fn ($value) => $value !== null,
                ),
            );
        }

        $this->form->fill($formData);
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
                    ->fetchFileInformation(false)
                    ->preserveFilenames()
                    ->required(),

                FileUpload::make('mobile_banner_image')
                    ->label('Банер (мобільна версія)')
                    ->image()
                    ->disk('public')
                    ->directory('homepage')
                    ->visibility('public')
                    ->fetchFileInformation(false)
                    ->preserveFilenames(),

                FileUpload::make('logo')
                    ->label('Логотип')
                    ->image()
                    ->disk('public')
                    ->directory('homepage')
                    ->visibility('public')
                    ->fetchFileInformation(false)
                    ->preserveFilenames(),

                Section::make('SMM і прев’ю посилань')
                    ->description('Ці дані бачать люди у Facebook, Telegram, Viber та інших сервісах, коли поширюють головне посилання сайту.')
                    ->schema([
                        TextInput::make('smm_title')
                            ->label('Заголовок для соцмереж')
                            ->maxLength(255)
                            ->helperText('За замовчуванням використовується назва ОМЦ.'),

                        Textarea::make('smm_description')
                            ->label('Короткий опис для соцмереж')
                            ->rows(3)
                            ->maxLength(200)
                            ->helperText('Рекомендовано до 200 символів.')
                            ->columnSpanFull(),

                        FileUpload::make('smm_image')
                            ->label('Головна SMM-обкладинка')
                            ->image()
                            ->disk('public')
                            ->directory('smm')
                            ->visibility('public')
                            ->helperText('Рекомендований розмір: 1200 × 630 px. Якщо не додавати, буде використано банер головної сторінки.')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                TextInput::make('contact_address')
                    ->label('Адреса')
                    ->maxLength(255),

                TextInput::make('contact_phone')
                    ->label('Телефон адміністратора')
                    ->tel()
                    ->maxLength(50),

                TextInput::make('contact_email')
                    ->label('Email для звернень')
                    ->email()
                    ->maxLength(255),

                TextInput::make('google_maps_url')
                    ->label('Посилання Google Maps')
                    ->url()
                    ->maxLength(2048)
                    ->helperText('Посилання використовується для вбудованої карти на сайті.'),

                Toggle::make('facebook_enabled')
                    ->label('Facebook')
                    ->live(),

                TextInput::make('facebook_url')
                    ->label('Facebook URL')
                    ->url()
                    ->hidden(fn ($get) => ! $get('facebook_enabled')),

                Toggle::make('instagram_enabled')
                    ->label('Instagram')
                    ->live(),

                TextInput::make('instagram_url')
                    ->label('Instagram URL')
                    ->url()
                    ->hidden(fn ($get) => ! $get('instagram_enabled')),

                Toggle::make('telegram_enabled')
                    ->label('Telegram')
                    ->live(),

                TextInput::make('telegram_url')
                    ->label('Telegram URL')
                    ->url()
                    ->hidden(fn ($get) => ! $get('telegram_enabled')),

                Toggle::make('youtube_enabled')
                    ->label('YouTube')
                    ->live(),

                TextInput::make('youtube_url')
                    ->label('YouTube URL')
                    ->url()
                    ->hidden(fn ($get) => ! $get('youtube_enabled')),

                Toggle::make('tiktok_enabled')
                    ->label('TikTok')
                    ->live(),

                TextInput::make('tiktok_url')
                    ->label('TikTok URL')
                    ->url()
                    ->hidden(fn ($get) => ! $get('tiktok_enabled')),

            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $settings = HomepageSetting::first() ?? new HomepageSetting;

        $settings->banner_image = $data['banner_image'] ?? null;

        $settings->mobile_banner_image = $data['mobile_banner_image'] ?? null;

        $settings->logo = $data['logo'] ?? null;

        $settings->smm_title = $data['smm_title'] ?? null;
        $settings->smm_description = $data['smm_description'] ?? null;
        $settings->smm_image = $data['smm_image'] ?? null;

        $settings->contact_address = $data['contact_address'] ?? null;
        $settings->contact_phone = $data['contact_phone'] ?? null;
        $settings->contact_email = $data['contact_email'] ?? null;
        $settings->google_maps_url = $data['google_maps_url'] ?? null;

        $settings->facebook_enabled = ! empty($data['facebook_enabled']);
        $settings->facebook_url = $data['facebook_url'] ?? null;

        $settings->instagram_enabled = ! empty($data['instagram_enabled']);
        $settings->instagram_url = $data['instagram_url'] ?? null;

        $settings->telegram_enabled = ! empty($data['telegram_enabled']);
        $settings->telegram_url = $data['telegram_url'] ?? null;

        $settings->youtube_enabled = ! empty($data['youtube_enabled']);
        $settings->youtube_url = $data['youtube_url'] ?? null;

        $settings->tiktok_enabled = ! empty($data['tiktok_enabled']);
        $settings->tiktok_url = $data['tiktok_url'] ?? null;

        $settings->save();

        $this->form->fill($settings->toArray());
    }
}
