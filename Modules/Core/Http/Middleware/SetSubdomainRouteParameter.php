<?php

namespace Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class SetSubdomainRouteParameter
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request):Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get the current subdomain from the request
        $subdomain = getCurrentSubdomain();

        if ($subdomain) {
            // Set the subdomain parameter for the current route
            Route::current()?->setParameter('subdomain', $subdomain);

            // Set URL defaults to help with URL generation
            URL::defaults(['subdomain' => $subdomain]);
        }

        return $next($request);
    }
}
