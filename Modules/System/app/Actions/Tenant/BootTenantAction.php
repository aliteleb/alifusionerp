<?php

namespace Modules\System\Actions\Tenant;

use Modules\Master\Entities\Facility;
use Modules\Core\Services\TenantDatabaseService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

/**
 * Boot Tenant Action
 *
 * Handles the initialization and configuration of tenant-specific settings
 * including database connection, filesystem configuration, URL management,
 * timezone settings, and CORS configuration.
 */
class BootTenantAction
{
    /**
     * Execute the tenant boot process.
     *
     * Detects the current facility and configures tenant-specific settings
     * including database connection, filesystem configuration, URL management,
     * timezone settings, and CORS configuration.
     */
    public static function execute(): void
    {
        // Detect the current facility from the request context
        $facility = TenantDatabaseService::getCurrentFacility();

        // Only proceed if a facility is detected
        if (! $facility) {
            return;
        }

        // Configure tenant settings for the detected facility
        self::configureTenant($facility);
    }

    /**
     * Configure tenant-specific settings for the given facility.
     */
    public static function configureTenant(Facility $facility): void
    {
        // Connect to the tenant database
        TenantDatabaseService::connectToFacility($facility);

        // Set tenant timezone
        self::setTenantTimezone();

        // Configure tenant URLs and filesystem
        self::configureTenantUrls($facility);

        // Configure tenant filesystem
        self::configureTenantFilesystem($facility);

        // Update CORS configuration
        self::updateCorsConfiguration($facility);

        // Refresh configuration cache
        self::refreshConfiguration();
    }

    /**
     * Configure tenant-specific URLs including subdomain handling.
     */
    public static function configureTenantUrls(Facility $facility): void
    {
        // Dynamically change the app URL to the subdomain
        $mainDomain = Config::get('app.url'); // Get the main domain from config

        // Extract the domain part (remove http:// or https://)
        $domain = preg_replace('#^https?://#', '', $mainDomain);

        // Create subdomain URL
        $subdomainUrl = 'https://'.$facility->subdomain.'.'.$domain;

        // Set the app URL to the subdomain
        Config::set('app.url', $subdomainUrl);

        // Also update the asset URL to match the subdomain
        Config::set('app.asset_url', $subdomainUrl);
    }

    /**
     * Configure tenant-specific filesystem settings.
     */
    public static function configureTenantFilesystem(Facility $facility): void
    {
        $subdomainUrl = Config::get('app.url');

        // Forget the public disk to ensure fresh configuration
        app('filesystem')->forgetDisk('public');

        // Create a dedicated file system disk for this tenant using subdomain
        $tenantDiskConfig = [
            'driver' => 'local',
            'root' => storage_path('app/public/tenants/'.$facility->subdomain),
            'url' => $subdomainUrl.'/storage/tenants/'.$facility->subdomain,
            'visibility' => 'public',
        ];

        // Set the tenant disk
        Config::set('filesystems.disks.tenant', $tenantDiskConfig);

        // Set this as the default disk for this tenant
        Config::set('filesystems.default', 'tenant');

        // Update the storage URL to use the subdomain [For CORS through main domain]
        Config::set('filesystems.disks.public.url', $subdomainUrl.'/storage');
    }

    /**
     * Update CORS configuration to include the tenant subdomain.
     */
    public static function updateCorsConfiguration(Facility $facility): void
    {
        $subdomainUrl = Config::get('app.url');

        // Update CORS configuration to include the subdomain
        $allowedOrigins = Config::get('cors.allowed_origins', []);

        if (! in_array($subdomainUrl, $allowedOrigins)) {
            $allowedOrigins[] = $subdomainUrl;
            Config::set('cors.allowed_origins', $allowedOrigins);
        }
    }

    /**
     * Set the tenant-specific timezone configuration.
     */
    public static function setTenantTimezone(): void
    {
        try {
            // Get timezone from tenant settings
            $timezone = \Modules\Core\Entities\Setting::where('key', 'timezone')->first()?->value ?? 'UTC';
            if ($timezone && in_array($timezone, timezone_identifiers_list())) {
                // Set application timezone
                Config::set('app.timezone', $timezone);

                // Set default timezone for PHP
                date_default_timezone_set($timezone);

                // Set timezone for Carbon
                Carbon::setLocale(app()->getLocale());
            }
        } catch (\Exception $e) {
            // Log error but don't break the application
            Log::warning('Failed to set tenant timezone: '.$e->getMessage());
        }
    }

    /**
     * Refresh configuration cache to ensure changes take effect.
     */
    protected static function refreshConfiguration(): void
    {
        // Refresh the configuration to ensure changes take effect
        if (app()->configurationIsCached()) {
            // Clear the config cache for this request to ensure our changes take effect
            Config::clearResolvedInstances();
        }
    }

    /**
     * Configure tenant-specific filesystem settings using current subdomain.
     *
     * This static method provides filesystem configuration without requiring
     * a facility object, using only the current subdomain from the request.
     * Useful for contexts where facility object is not available but subdomain
     * detection is sufficient.
     */
    public static function configureTenantFilesystemBySubdomain(): void
    {
        // Get current subdomain from request
        $subdomain = TenantDatabaseService::getCurrentSubdomain();

        // Only proceed if a subdomain is detected
        if (! $subdomain) {
            return;
        }

        // Get current app URL (should already be configured for tenant)
        $subdomainUrl = 'https://'.$subdomain.'.'.config('app.domain');

        // Forget the public disk to ensure fresh configuration
        app('filesystem')->forgetDisk('public');

        // Create a dedicated file system disk for this tenant using subdomain
        $tenantDiskConfig = [
            'driver' => 'local',
            'root' => storage_path('app/public/tenants/'.$subdomain),
            'url' => $subdomainUrl.'/storage/tenants/'.$subdomain,
            'visibility' => 'public',
        ];

        // Set the tenant disk
        Config::set('filesystems.disks.tenant', $tenantDiskConfig);

        // Set this as the default disk for this tenant
        Config::set('filesystems.default', 'tenant');

        // Update the storage URL to use the subdomain [For CORS through main domain]
        Config::set('filesystems.disks.public.url', $subdomainUrl.'/storage');
    }
}
