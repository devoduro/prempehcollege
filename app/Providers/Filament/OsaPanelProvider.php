<?php

namespace App\Providers\Filament;

use App\Filament\Osa\Pages\Dashboard;
use App\Filament\Osa\Widgets\OsaCheckinsChart;
use App\Filament\Osa\Widgets\OsaHouseChart;
use App\Filament\Osa\Widgets\OsaRecentCheckins;
use App\Filament\Osa\Widgets\OsaStatTiles;
use App\Filament\Osa\Widgets\OsaWelcome;
use App\Filament\Osa\Widgets\OsaYearChart;
use Filament\Enums\ThemeMode;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
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

/** Old Students Association portal: its own login, guard and admins, apart from the school website admin. */
class OsaPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('osa')
            ->path('osa')
            ->authGuard('osa')
            ->login()
            ->brandName('Prempeh Old Students')
            ->favicon(asset('images/favicon.png'))
            ->defaultThemeMode(ThemeMode::Light)
            ->colors(['primary' => Color::hex('#115e03'), 'warning' => Color::hex('#ffc53a'), 'gray' => Color::Slate])
            ->navigationItems([
                NavigationItem::make('Old Students page')->url(fn () => route('osa.home'), shouldOpenInNewTab: true)
                    ->icon('heroicon-o-globe-alt')->sort(99),
            ])
            ->discoverResources(in: app_path('Filament/Osa/Resources'), for: 'App\\Filament\\Osa\\Resources')
            ->pages([Dashboard::class])
            ->widgets([OsaWelcome::class, OsaStatTiles::class, OsaCheckinsChart::class, OsaHouseChart::class, OsaYearChart::class, OsaRecentCheckins::class])
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
