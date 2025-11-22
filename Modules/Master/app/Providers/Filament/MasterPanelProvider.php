<?php

namespace Modules\Master\Providers\Filament;

use Modules\Master\Filament\Master\Pages\DatabaseManager;
use Modules\Master\Filament\Master\Pages\Settings;
use Modules\Master\Http\Middleware\Panels\MasterPanelAuthenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
// use SolutionForest\FilamentTranslateField\FilamentTranslateFieldPlugin;
use Coolsam\Modules\ModulesPlugin;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class MasterPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('master')
            ->path('master')
            ->domain(config('app.domain'))
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->breadcrumbs(false)
            ->brandName(settings('app_name', 'Ali Fusion ERP'))
            // ->brandLogo(settings('logo'))
            // ->brandLogo(fn() => view('filament.components.custom-brand'))
            ->homeUrl('/')
            ->font('Noto Sans Arabic')
            ->profile(isSimple: false)
            ->maxContentWidth(Width::Full)
            ->sidebarWidth('300px')
            ->sidebarCollapsibleOnDesktop()
            ->spa()
            ->databaseNotifications()
            // ->databaseTransactions()
            ->unsavedChangesAlerts()
            ->renderHook(
                'panels::head.end',
                fn (): string => '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />'
            )
            ->renderHook(
                'panels::footer',
                fn (): string => view('filament.components.copyright-footer')->render()
            )
            ->viteTheme('resources/css/filament/master/theme.css')
            ->discoverResources(in: module_path('Master', 'app/Filament/Master/Resources'), for: 'Modules\\Master\\Filament\\Master\\Resources')
            ->pages([
                Dashboard::class,
                DatabaseManager::class,
                Settings::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Master/Widgets'), for: 'App\\Filament\\Master\\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->navigationGroups([
                NavigationGroup::make()->label(fn () => __('Facilities')),
                NavigationGroup::make()->label(fn () => __('System Management')),
                NavigationGroup::make()->label(fn () => __('Administration')),
            ])
            ->plugins([
                ModulesPlugin::make(),
                // FilamentTranslateFieldPlugin::make()->defaultLocales(appLocales()),
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
                MasterPanelAuthenticate::class,
            ]);
    }
}

