<?php

use Modules\Master\Entities\Facility;
use Modules\Core\Services\TenantDatabaseService;

if (! function_exists('tenant_context')) {
    /**
     * Execute a callback within a tenant context
     *
     * @return mixed
     */
    function tenant_context(Facility $facility, callable $callback)
    {
        return TenantDatabaseService::withinTenant($facility, $callback);
    }
}

if (! function_exists('switch_to_tenant')) {
    /**
     * Switch to a tenant database connection
     */
    function switch_to_tenant(Facility $facility): void
    {
        TenantDatabaseService::switchToTenant($facility);
    }
}

if (! function_exists('switch_to_default')) {
    /**
     * Switch back to the default database connection
     */
    function switch_to_default(): void
    {
        TenantDatabaseService::switchToMaster();
    }
}

if (! function_exists('current_tenant_connection')) {
    /**
     * Get the current tenant connection name
     */
    function current_tenant_connection(): ?string
    {
        return TenantDatabaseService::getCurrentTenantConnection();
    }
}

if (! function_exists('is_on_tenant')) {
    /**
     * Check if currently on a tenant connection
     */
    function is_on_tenant(): bool
    {
        return TenantDatabaseService::isOnTenantConnection();
    }
}

if (! function_exists('tenant_database_name')) {
    /**
     * Get tenant database name for a facility
     */
    function tenant_database_name(Facility $facility): string
    {
        return TenantDatabaseService::getTenantDatabaseName($facility);
    }
}

if (! function_exists('tenant_database_exists')) {
    /**
     * Check if tenant database exists for a facility
     */
    function tenant_database_exists(Facility $facility): bool
    {
        return TenantDatabaseService::tenantDatabaseExists($facility);
    }
}

if (! function_exists('get_current_tenant')) {
    /**
     * Get the current tenant facility
     */
    function get_current_tenant(): ?Facility
    {
        return config('tenant.current_facility');
    }
}

if (! function_exists('detect_tenant_by_subdomain')) {
    /**
     * Detect tenant by subdomain
     */
    function detect_tenant_by_subdomain(?string $subdomain = null): ?Facility
    {
        return TenantDatabaseService::detectAndSwitchTenant($subdomain);
    }
}

if (! function_exists('get_current_subdomain')) {
    /**
     * Get current subdomain from request
     */
    function get_current_subdomain(): ?string
    {
        return TenantDatabaseService::getCurrentSubdomain();
    }
}
