<?php

namespace App\Providers;

use Modules\System\Actions\Tenant\BootTenantAction;
use Illuminate\Support\ServiceProvider;

/**
 * Tenant Service Provider
 *
 * Handles tenant-specific service registration and bootstrapping.
 * Delegates tenant configuration to the BootTenantAction for better
 * separation of concerns and maintainability.
 */
class TenantServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register the tenant database service
        // $this->app->singleton(TenantDatabaseService::class);
    }

    /**
     * Bootstrap services.
     *
     * Initializes tenant-specific configuration by delegating to BootTenantAction.
     * The action handles facility detection and tenant setup internally.
     */
    public function boot(): void
    {
        // Connect to the current facility
        \Modules\Core\Services\TenantDatabaseService::connectToCurrentFacility();

        // Delegate all tenant configuration to the action class
        // The action will handle facility detection and configuration internally
        (new BootTenantAction)->execute();
    }
}
