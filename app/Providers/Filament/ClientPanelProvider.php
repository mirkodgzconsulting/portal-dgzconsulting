<?php

namespace App\Providers\Filament;

use App\Filament\Cliente\Pages\EditProfile;
use App\Filament\Cliente\Pages\Login;
use App\Http\Middleware\AuthenticateClientOrEditor;
use App\Models\Client;
use App\Curator\ClientMediaResource;
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
use App\Filament\Cliente\Widgets\WelcomeWidget;
use App\Filament\Cliente\Widgets\RecentPostsWidget;
use App\Filament\Cliente\Widgets\SubscriptionsWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class ClientPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('cliente')
            ->path('area-cliente')
            ->authGuard('client')
            ->login(Login::class)
            ->profile(EditProfile::class)
            ->brandName('DGZ Consulting')
            ->brandLogo(view('components.brand-logo'))
            ->brandLogoHeight('3rem')
            ->favicon(asset('icono-dgzconsulting-png.png'))
            ->viteTheme('resources/css/filament/cliente/theme.css')
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
                fn () => Auth::guard('client')->check()
                    ? '<div class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-200">' . e(Auth::guard('client')->user()->name) . '</div>'
                    : (Auth::guard('client_user')->check()
                        ? '<div class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-200">' . e(Auth::guard('client_user')->user()->name) . ' <span class="ml-1 text-xs text-gray-400">(editor)</span></div>'
                        : '')
            )
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn () => '<link href="https://fonts.googleapis.com/css2?family=Urbanist:wght@400;500;600;700;800&display=swap" rel="stylesheet"><script src="https://cdn.lordicon.com/lordicon.js"></script>'
            )
            ->discoverResources(in: app_path('Filament/Cliente/Resources'), for: 'App\Filament\Cliente\Resources')
            ->pages([
                Dashboard::class,
            ])
            ->widgets([
                WelcomeWidget::class,
                RecentPostsWidget::class,
                SubscriptionsWidget::class,
            ])
            ->plugins([
                SeoPlugin::make()
                    ->enableManagementPage(false)
                    ->enableDashboardWidget(false),
                CuratorPlugin::make()
                    ->label('Media')
                    ->pluralLabel('Media Library')
                    ->navigationIcon('geist-image')
                    ->navigationGroup('Contenido')
                    ->navigationSort(2),
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
                AuthenticateClientOrEditor::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
