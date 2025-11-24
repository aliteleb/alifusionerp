<?php

namespace Modules\Survey\Providers\Filament;

use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Modules\Core\Filament\Pages\Auth\Login;
use Modules\Core\Filament\Pages\Dashboard;
use Modules\Core\Filament\Widgets\ActivityLogWidget;
use Modules\Core\Filament\Widgets\CustomAccountWidget;
use Modules\Core\Http\Middleware\Panels\AdminPanelAuthenticate;
use Modules\Core\Http\Middleware\SetSubdomainRouteParameter;
use Modules\Core\Http\Middleware\TenantDatabaseMiddleware;
use Modules\Core\Http\Middleware\TrackUserActivity;
use Modules\Survey\Filament\Pages\SurveySettings;

class SurveyPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $panel = $panel
            ->default()
            ->id('survey')
            ->path('survey')
            ->authGuard('web')
            ->domain('{subdomain}.'.config('app.domain'))
            ->login(Login::class)
            ->colors([
                'primary' => Color::Purple,
            ])
            ->breadcrumbs(false)
            ->brandName(fn (): string => settings('app_name').' · '.__('Survey'))
            ->brandLogo(fn () => settings('logo'))
            ->favicon(fn () => settings('favicon'))
            ->brandLogoHeight('40px')
            ->homeUrl('/')
            ->font('Noto Sans Arabic')
            ->profile(isSimple: false)
            ->maxContentWidth(Width::Full)
            ->sidebarWidth('320px')
            ->sidebarCollapsibleOnDesktop()
            ->spa()
            ->databaseNotifications()
            ->databaseTransactions()
            ->databaseNotificationsPolling('45s')
            ->unsavedChangesAlerts()
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->renderHook(
                'panels::head.end',
                fn (): string => '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />'
                    .'<link rel="apple-touch-icon" sizes="180x180" href="'.(settings('logo') ?: settings('favicon') ?: asset('favicon.ico')).'" />'
                    .'<link rel="icon" type="image/png" sizes="32x32" href="'.(settings('favicon') ?: asset('favicon.ico')).'" />'
                    .'<link rel="icon" type="image/png" sizes="16x16" href="'.(settings('favicon') ?: asset('favicon.ico')).'" />'
                    .'<link rel="manifest" href="'.route('subdomain.manifest', getCurrentSubdomain()).'" />'
                    .'<meta name="theme-color" content="#ffffff" />'
            )
            ->renderHook(
                'panels::footer',
                fn (): string => view('filament.components.copyright-footer')->render()
            )
            ->pages([
                Dashboard::class,
                SurveySettings::class,
            ])
            ->widgets([
                CustomAccountWidget::class,
                ActivityLogWidget::class,
            ]);

        foreach ($this->getModulesForDiscovery() as $moduleName) {
            if (! $this->moduleExists($moduleName)) {
                continue;
            }

            $moduleBasePath = base_path('Modules/'.$moduleName);
            $namespace = "Modules\\{$moduleName}\\Filament";

            $resourcesPath = $moduleBasePath.'/app/Filament/Resources';
            if (is_dir($resourcesPath)) {
                $panel = $panel->discoverResources($resourcesPath, "{$namespace}\\Resources");
            }

            $pagesPath = $moduleBasePath.'/app/Filament/Pages';
            if (is_dir($pagesPath)) {
                $panel = $panel->discoverPages($pagesPath, "{$namespace}\\Pages");
            }

            $widgetsPath = $moduleBasePath.'/app/Filament/Widgets';
            if (is_dir($widgetsPath)) {
                $panel = $panel->discoverWidgets($widgetsPath, "{$namespace}\\Widgets");
            }
        }

        return $panel
            ->navigationGroups([
                NavigationGroup::make()->label(fn () => __('Survey Management')),
                NavigationGroup::make()->label(fn () => __('Survey Operations')),
                NavigationGroup::make()->label(fn () => __('Reports & Analytics')),
                NavigationGroup::make()->label(fn () => __('User Management')),
                NavigationGroup::make()->label(fn () => __('Organization')),
                NavigationGroup::make()->label(fn () => __('System')),
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
                SetSubdomainRouteParameter::class,
                TenantDatabaseMiddleware::class,
                TrackUserActivity::class,
            ])
            ->authMiddleware([
                AdminPanelAuthenticate::class,
            ]);
    }

    /**
     * Core resources should always be available alongside survey resources.
     *
     * @return array<int, string>
     */
    protected function getModulesForDiscovery(): array
    {
        return ['Core', 'Survey'];
    }

    private function moduleExists(string $moduleName): bool
    {
        return is_dir(base_path('Modules/'.$moduleName));
    }
}
