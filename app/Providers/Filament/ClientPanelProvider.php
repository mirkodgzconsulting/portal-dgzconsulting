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
use App\Filament\Cliente\Pages\Dashboard;
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
                    50  => '#eef4ff',
                    100 => '#d9e6ff',
                    200 => '#bcd3ff',
                    300 => '#8eb5ff',
                    400 => '#5a8ef7',
                    500 => '#3470ee',
                    600 => '#0F65E6',
                    700 => '#0d54c4',
                    800 => '#10449f',
                    900 => '#133c7d',
                    950 => '#0e254d',
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
                fn () => '<link href="https://fonts.googleapis.com/css2?family=Urbanist:wght@400;500;600;700;800&display=swap" rel="stylesheet"><script src="https://cdn.lordicon.com/lordicon.js"></script><link rel="preload" href="/icons/wired-outline-269-avatar-female-hover-jump.json" as="fetch" crossorigin><link rel="preload" href="/icons/wired-outline-268-avatar-man-hover-jump.json" as="fetch" crossorigin>'
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
            ->discoverPages(in: app_path('Filament/Cliente/Pages'), for: 'App\Filament\Cliente\Pages')
            ->plugins([
                SeoPlugin::make()
                    ->enableManagementPage(false)
                    ->enableDashboardWidget(false),
                CuratorPlugin::make()
                    ->registerNavigation(false),
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
                AuthenticateClientOrEditor::class,
            ]);
    }
}
