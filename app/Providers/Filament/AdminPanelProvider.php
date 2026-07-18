<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

// РЕСУРСИ
use App\Filament\Resources\Events\EventResource;
use App\Filament\Resources\EventSummaries\EventSummaryResource;
use App\Filament\Resources\UserResource;
use App\Filament\Resources\SystemLogs\SystemLogResource;
use App\Filament\Resources\Employees\EmployeeResource;
use App\Filament\Resources\Statutes\StatuteResource;
use App\Filament\Resources\Reports\ReportResource;
use App\Filament\Resources\CalendarPlans\CalendarPlanResource;

// СТОРІНКИ
use App\Filament\Pages\HomepageSettings;
use App\Filament\Pages\FooterSettings;
use App\Filament\Pages\HelpGuide;
use App\Models\HomepageSetting;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->brandName('ОМЦ')
            ->brandLogo(function (): string {
                $logo = HomepageSetting::query()->value('logo');

                return $logo
                    ? asset('storage/' . $logo)
                    : asset('storage/homepage/Лого ПОМЦ.png');
            })
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
                \App\Filament\Pages\Dashboard::class,
                HomepageSettings::class,
                FooterSettings::class,
                HelpGuide::class,
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
