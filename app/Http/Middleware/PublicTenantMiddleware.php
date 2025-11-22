<?php

namespace App\Http\Middleware;

use Modules\Core\Services\TenantDatabaseService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PublicTenantMiddleware
{
    /**
     * Handle an incoming request for public tenant routes.
     *
     * This middleware automatically detects the tenant based on subdomain
     * and switches to the appropriate tenant database.
     *
     * @param  Closure(Request):Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get current subdomain
        $subdomain = TenantDatabaseService::getCurrentSubdomain();

        if ($subdomain) {
            // Detect and switch to tenant database
            $facility = TenantDatabaseService::detectAndSwitchTenant($subdomain);

            if (! $facility) {
                // If no facility found for subdomain, return 404
                abort(404, __('Organization not found.'));
            }

            // Set locale if configured in facility settings
            if ($facility->default_locale) {
                app()->setLocale($facility->default_locale);
            }

            // Store current facility in request for easy access
            $request->attributes->set('current_facility', $facility);
        } else {
            // No subdomain detected, redirect to main domain or show error
            abort(404, __('Please access via your organization subdomain.'));
        }

        $response = $next($request);

        // Optional: Switch back to default connection after request
        // TenantDatabaseService::switchToMaster();

        return $response;
    }
}
