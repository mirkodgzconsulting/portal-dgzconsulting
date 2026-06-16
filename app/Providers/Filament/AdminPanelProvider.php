<?php

namespace App\Providers\Filament;

use Awcodes\Curator\CuratorPlugin;
use Filament\Enums\ThemeMode;
use Filament\Http\Middleware\Authenticate;
use Filament\Support\Enums\Width;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Icons\Heroicon;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
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
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->login()
            ->brandName('DGZ Consulting · Admin')
            ->brandLogo(asset('DGZConsulting-Logo-Slogan-v2.png'))
            ->brandLogoHeight('2.5rem')
            ->favicon(asset('Favicon-DGZConsulting-Squared.png'))
            ->colors([
                'primary' => [
                    50  => '#eff6ff',
                    100 => '#dbeafe',
                    200 => '#bfdbfe',
                    300 => '#93c5fd',
                    400 => '#60a5fa',
                    500 => '#3b82f6',
                    600 => '#0F65E6',
                    700 => '#0d56c4',
                    800 => '#0a3f8f',
                    900 => '#082d66',
                    950 => '#051c40',
                ],
            ])
            ->defaultThemeMode(ThemeMode::Light)
            ->sidebarWidth('15rem')
            ->maxContentWidth(Width::Full)
            ->renderHook(
                PanelsRenderHook::USER_MENU_BEFORE,
                fn () => Auth::check()
                    ? '<div class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-200">' . e(Auth::user()->name) . '</div>'
                    : ''
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
            ])
            ->plugins([
                CuratorPlugin::make()
                    ->label('Media')
                    ->pluralLabel('Biblioteca de medios')
                    ->navigationIcon(Heroicon::OutlinedPhoto)
                    ->navigationGroup('Contenido')
                    ->navigationSort(2)
                    ->showBadge(true)
                    ->registerNavigation(true)
                    ->curations(true),
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
