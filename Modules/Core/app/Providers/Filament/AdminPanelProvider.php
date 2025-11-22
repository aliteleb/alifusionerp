<?php

namespace Modules\Core\Providers\Filament;

use Coolsam\Modules\ModulesPlugin;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\Widgets;
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

class AdminPanelProvider extends PanelProvider
{
    /**
     * Get modules that should have their Filament resources discovered
     */
    protected function getModulesForDiscovery(): array
    {
        return ['Core', 'Organization', 'UserManagement', 'ActivityLog', 'Settings', 'Backup'];
    }

    /**
     * Check if a module exists
     */
    protected function moduleExists(string $moduleName): bool
    {
        // Check if module directory exists directly
        $modulePath = base_path('Modules/'.$moduleName);

        return is_dir($modulePath);
    }

    public function panel(Panel $panel): Panel
    {
        $panel = $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->authGuard('web')
            ->domain('{subdomain}.'.config('app.domain'))
            ->login(Login::class)
            ->colors([
                'primary' => Color::Sky,
            ])
            ->breadcrumbs(false)
            ->brandName(fn () => settings('app_name'))
            // ->brandLogo(fn () => view('filament.components.custom-brand'))
            ->brandLogo(fn () => settings('logo'))
            ->favicon(fn () => settings('favicon'))
            ->brandLogoHeight('40px')
            ->homeUrl('/')
            ->font('Noto Sans Arabic')
            ->profile(isSimple: false)
            ->maxContentWidth(Width::Full)
            ->sidebarWidth('300px')
            ->sidebarCollapsibleOnDesktop()
            ->spa()
            ->databaseNotifications()
            ->databaseTransactions()
            ->databaseNotificationsPolling('30s')
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
                    .'<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>'
            )
            ->renderHook(
                'panels::footer',
                fn (): string => view('filament.components.copyright-footer')->render()
            )
            ->renderHook(
                'panels::scripts.before',
                fn (): string => view('filament.components.vite-assets')->render()
            )
            ->renderHook(
                'panels::body.end',
                fn (): string => config('webpush.enabled', false)
                    ? view('filament.components.push-notification-script')->render()
                    : ''
            )
            ->pages([
                Dashboard::class,
            ])
            ->widgets([
                // Widgets\AccountWidget::class,
                CustomAccountWidget::class,
                // ActivityLogWidget::class,
            ]);

        // Discover resources, pages, and widgets from all existing modules
        // Chain the discovery calls to ensure they're properly registered
        foreach ($this->getModulesForDiscovery() as $moduleName) {
            if (! $this->moduleExists($moduleName)) {
                continue;
            }

            // Build paths directly to avoid calling module_path() which may fail
            // Use forward slashes as Filament expects them
            $moduleBasePath = base_path('Modules/'.$moduleName);
            $resourcesPath = $moduleBasePath.'/app/Filament/Resources';
            $pagesPath = $moduleBasePath.'/app/Filament/Pages';
            $widgetsPath = $moduleBasePath.'/app/Filament/Widgets';
            $namespace = "Modules\\{$moduleName}\\Filament";

            if (is_dir($resourcesPath)) {
                $panel = $panel->discoverResources(in: $resourcesPath, for: "{$namespace}\\Resources");
            }

            if (is_dir($pagesPath)) {
                $panel = $panel->discoverPages(in: $pagesPath, for: "{$namespace}\\Pages");
            }

            if (is_dir($widgetsPath)) {
                $panel = $panel->discoverWidgets(in: $widgetsPath, for: "{$namespace}\\Widgets");
            }
        }

        // Continue the chain with navigation and other settings
        return $panel
            ->navigationGroups([
                NavigationGroup::make()->label(fn () => __('Organization')),
                NavigationGroup::make()->label(fn () => __('Ali Fusion ERP')),
                NavigationGroup::make()->label(fn () => __('Reports & Analytics')),
                NavigationGroup::make()->label(fn () => __('User Management')),
                NavigationGroup::make()->label(fn () => __('Operations')),
                NavigationGroup::make()->label(fn () => __('Reports')),
                NavigationGroup::make()->label(fn () => __('Administration')),
                NavigationGroup::make()->label(fn () => __('System')),
            ])
            ->plugins([
                ModulesPlugin::make(),
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
                SetSubdomainRouteParameter::class, // Add subdomain parameter handling
                TenantDatabaseMiddleware::class, // Add this line
                TrackUserActivity::class, // Track user activity for online status
            ])
            ->authMiddleware([
                AdminPanelAuthenticate::class,
            ]);
    }
}
