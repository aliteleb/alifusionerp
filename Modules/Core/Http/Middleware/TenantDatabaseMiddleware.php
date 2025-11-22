<?php

namespace Modules\Core\Http\Middleware;

use Modules\Core\Services\TenantDatabaseService;
use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantDatabaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request):Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = null;

        // Try to get tenant from Filament context first
        try {
            $tenant = Filament::getTenant();
        } catch (\Exception $e) {
            // If Filament context is not available, try subdomain-based detection
            $subdomain = $request->route('subdomain');
            if ($subdomain) {
                $tenant = \Modules\Master\Entities\Facility::where('subdomain', $subdomain)->first();
            }
        }

        if ($tenant) {
            // Switch to tenant database
            TenantDatabaseService::switchToTenant($tenant);
        }

        $response = $next($request);

        // Switch back to default connection after request
        if ($tenant) {
            TenantDatabaseService::switchToMaster();
        }

        return $response;
    }
}
