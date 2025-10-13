<?php

namespace App\Providers\Filament;

use App\Http\Middleware\RoleMiddleware;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\Support\Facades\Auth;

class TreasurerPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('treasurer')
            ->path('treasurer')
            ->colors([
                'primary' => Color::Green,
            ])
            // untuk nama panel dan logo web
            ->brandName('Misadaku School')
            ->brandLogo(fn() => view('components.custom-brand'))
            ->favicon(asset('storage/images/misadaku.png'))
            ->renderHook(
                'panels::user-menu.before',
                fn(): string => '<span class="mr-2 font-semibold text-gray-700 dark:text-white">' . ucwords(Auth::user()->name) . '</span>'
            )
            ->discoverResources(in: app_path('Filament/Treasurer/Resources'), for: 'App\\Filament\\Treasurer\\Resources')
            ->discoverPages(in: app_path('Filament/Treasurer/Pages'), for: 'App\\Filament\\Treasurer\\Pages')
            ->pages([
                // Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Treasurer/Widgets'), for: 'App\\Filament\\Treasurer\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
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
                // Authenticate::class,
                'web',
                'auth',
                RoleMiddleware::class . ':treasurer'
            ]);
    }
}
