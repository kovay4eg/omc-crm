<?php

namespace App\Providers\Filament;

use App\Filament\Pages\AdminProMail;
use App\Filament\Pages\Dashboard;
use App\Filament\Pages\FooterSettings;
use App\Filament\Pages\HelpGuide;
use App\Filament\Pages\HomepageSettings;
use App\Filament\Resources\CalendarPlans\CalendarPlanResource;
use App\Filament\Resources\Employees\EmployeeResource;
use App\Filament\Resources\Events\EventResource;
use App\Filament\Resources\EventSummaries\EventSummaryResource;
use App\Filament\Resources\Reports\ReportResource;
use App\Filament\Resources\Statutes\StatuteResource;
use App\Filament\Resources\SystemLogs\SystemLogResource;
use App\Filament\Resources\UserResource;
use App\Models\HomepageSetting;
use App\Support\MediaUrl;
// РЕСУРСИ
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
// СТОРІНКИ
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->brandName('ОМЦ')
            ->favicon(asset('favicon-omc.png'))
            ->brandLogo(function (): string {
                $logo = HomepageSetting::query()->value('logo');

                return $logo
                    ? MediaUrl::storage($logo)
                    : MediaUrl::storage('homepage/Лого ПОМЦ.png');
            })
            ->darkModeBrandLogo(asset('images/logo-white.png'))
            ->brandLogoHeight('3.5rem')

            ->assets([
                Css::make(
                    'fullcalendar-css',
                    'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css'
                ),

                Js::make(
                    'fullcalendar-js',
                    'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'
                ),
                Js::make('firebase-app', 'https://www.gstatic.com/firebasejs/10.14.1/firebase-app-compat.js'),
                Js::make('firebase-messaging', 'https://www.gstatic.com/firebasejs/10.14.1/firebase-messaging-compat.js'),
                Js::make('admin-pro-mail-push', asset('js/admin-pro-mail-push.js')),
            ])

            ->login()

            ->renderHook(
                'panels::body.start',
                fn () => view('filament.components.preview-banner')
            )

            ->colors([
                'primary' => Color::Amber,
            ])

            ->pages([
                Dashboard::class,
                HomepageSettings::class,
                FooterSettings::class,
                HelpGuide::class,
                AdminProMail::class,
            ])

            ->resources([
                EventResource::class,
                EventSummaryResource::class,
                UserResource::class,
                SystemLogResource::class,
                EmployeeResource::class,
                StatuteResource::class,
                ReportResource::class,
                CalendarPlanResource::class,
            ])

            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])

            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
