<?php

namespace App\Providers\Filament;

use Awcodes\Curator\CuratorPlugin;
use Nomanur\FilamentSeoPro\SeoPlugin;
use Filament\Enums\ThemeMode;
use Filament\Http\Middleware\Authenticate;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
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
            ->brandName('DGZ Consulting')
            ->brandLogo(view('components.brand-logo'))
            ->brandLogoHeight('3rem')
            ->favicon(asset('icono-dgzconsulting-png.png'))
            ->colors([
                'primary' => [
                    50  => '#eff6ff',
                    100 => '#dbeafe',
                    200 => '#bdd4fe',
                    300 => '#93bbfd',
                    400 => '#5a9cf9',
                    500 => '#3483f5',
                    600 => '#0070f3',
                    700 => '#0060d1',
                    800 => '#0550ad',
                    900 => '#0a3d82',
                    950 => '#072556',
                ],
                'gray' => Color::Zinc,
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
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn () => '<link href="https://fonts.googleapis.com/css2?family=Urbanist:wght@400;500;600;700;800&display=swap" rel="stylesheet">'
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
                SeoPlugin::make(),
                CuratorPlugin::make()
                    ->label('Media')
                    ->pluralLabel('Biblioteca de medios')
                    ->navigationIcon('phosphor-image-light')
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
