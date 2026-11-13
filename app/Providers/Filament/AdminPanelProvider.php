<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Enums\ThemeMode;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Filament\PanelProvider;
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
            // Set ADMIN_PATH to something less guessable than "admin" in production.
            ->path(config('admin.path'))
            ->login()
            ->profile(isSimple: false)
            ->multiFactorAuthentication(
                [AppAuthentication::make()->recoverable()->brandName(config('site.name'))],
                isRequired: (bool) config('admin.require_mfa'),
            )
            ->brandName('Prempeh College Admin')
            ->favicon(asset('images/favicon.png'))
            // Light page with a dark sidebar, like jhs_portal; users can still switch themes from their menu.
            ->defaultThemeMode(ThemeMode::Light)
            ->colors([
                'primary' => Color::hex('#115e03'),
                'warning' => Color::hex('#ffc53a'),
                'gray' => Color::Slate,
            ])
            // Dark slate sidebar and portal-style cards (see resources/css/filament/admin/theme.css).
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->navigationItems([
                NavigationItem::make('View website')->url(fn () => url('/'), shouldOpenInNewTab: true)
                    ->icon('heroicon-o-globe-alt')->group('Site')->sort(99),
            ])
            ->navigationGroups([
                NavigationGroup::make('Content'),
                NavigationGroup::make('People'),
                NavigationGroup::make('Inbox'),
                NavigationGroup::make('Site'),
                NavigationGroup::make('Security'),
            ])
            ->unsavedChangesAlerts()
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([Dashboard::class])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
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
            ->authMiddleware([Authenticate::class]);
    }
}
