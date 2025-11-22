<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class LocalizationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request):Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get available locales
        $availableLocales = array_keys(config('filament-language-switcher.locals', ['en' => ['label' => 'English']]));

        // Check for locale parameter in URL first
        if ($request->has('locale')) {
            $requestedLocale = $request->get('locale');

            if (in_array($requestedLocale, $availableLocales)) {
                // Store locale in session
                session(['locale' => $requestedLocale]);
                App::setLocale($requestedLocale);

                // Redirect to clean URL without locale parameter
                $url = $request->url();
                $params = $request->except('locale');
                if (! empty($params)) {
                    $url .= '?'.http_build_query($params);
                }

                return redirect($url);
            }
        }

        // Set locale based on authenticated user's language preference
        if (Auth::check()) {
            $user = Auth::user();

            // Try to get user's language preference from user_languages table
            try {
                $userLanguage = DB::table('user_languages')
                    ->where('model_type', get_class($user))
                    ->where('model_id', $user->id)
                    ->first();

                if ($userLanguage && $userLanguage->lang) {
                    $locale = $userLanguage->lang;

                    // Validate locale against available locales
                    $availableLocales = array_keys(config('filament-language-switcher.locals', ['en' => ['label' => 'English']]));

                    if (in_array($locale, $availableLocales)) {
                        App::setLocale($locale);
                    }
                }
            } catch (Exception $e) {
                // If user_languages table doesn't exist or there's an error,
                // fall back to session or default locale
            }
        }

        // Check for session locale (for all users, authenticated or not)
        if (! isset($locale)) {
            $sessionLocale = session('locale');
            if ($sessionLocale && in_array($sessionLocale, $availableLocales)) {
                App::setLocale($sessionLocale);
            }
        }

        return $next($request);
    }
}
