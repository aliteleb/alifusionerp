<?php

use Modules\Core\Helpers\SEO;
use Modules\Core\Entities\Currency;
use Modules\Master\Entities\Facility;
use Modules\Core\Entities\Setting;
use Modules\Core\Entities\User;
use Modules\Core\Services\TenantDatabaseService;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

function settings(?string $key = null, $default = null)
{
    $subdomain = getCurrentSubdomain();

    // Return default if we're running in console or if no subdomain
    if (app()->runningInConsole() || ! $subdomain) {
        return $default;
    }

    if (! $subdomain) {
        return $default;
    }
    $cacheKey = $subdomain.'_settings';

    if (! Cache::has($cacheKey)) {
        loadSettings();
    }

    $settings = Cache::get($cacheKey);

    if (is_null($key)) {
        return $settings;
    }

    $localeKey = $key.'_'.app()->getLocale();

    // Return the localized version if it exists, fall back to the default key,
    // and finally return the provided default value
    return $settings[$localeKey] ?? $settings[$key] ?? $default;
}
function loadSettings(): void
{
    // Connect to the current facility
    TenantDatabaseService::connectToCurrentFacility();

    // Get tenant
    $subdomain = getCurrentSubdomain();
    $cacheKey = $subdomain.'_settings';

    if (! $subdomain) {
        return;
    }

    // The action will handle facility detection and configuration internally
    \App\Core\Actions\Tenant\BootTenantAction::configureTenantFilesystemBySubdomain();

    // Get all settings as a collection and convert to array
    $all_settings = \App\Core\Models\Setting::pluck('value', 'key')->toArray();

    // Handle logo and icon URLs
    $logoKeys = ['logo', 'sidebar_logo', 'sidebar_collapsed_logo', 'login_image', 'favicon'];
    foreach ($logoKeys as $imageKey) {
        if (isset($all_settings[$imageKey]) && ! empty($all_settings[$imageKey])) {
            // Generate URL using asset helper for storage paths
            $all_settings[$imageKey] = Storage::disk('tenant')->url($all_settings[$imageKey]);
        } else {
            $all_settings[$imageKey] = null;
        }
    }

    // Handle locales (JSON to array)
    $all_settings['locales'] = isset($all_settings['locales'])
        ? (json_decode($all_settings['locales'], true) ?: [])
        : [];

    // Handle app_tags (JSON to comma-separated string)
    $tags = isset($all_settings['app_tags'])
        ? (json_decode($all_settings['app_tags'], true) ?: [])
        : [];

    $all_settings['app_tags'] = is_array($tags) ? implode(', ', $tags) : '';

    // Handle currency - check if currencies table exists
    $currency = \App\Core\Models\Currency::find($all_settings['currency_id'] ?? 1);
    $all_settings['currency'] = $currency?->symbol ?? '$';
    Cache::put($cacheKey, $all_settings, now()->addDay());

}
function get_setting(?string $key = null, $default = null)
{
    // Return default if we're running in console
    if (app()->runningInConsole()) {
        return $default;
    }

    // check if settings table does not exist
    if (! Schema::hasTable('settings')) {
        return $default;
    }

    // Get tenant
    $subdomain = getCurrentSubdomain();
    $cacheKey = $subdomain ? $subdomain.'_settings' : 'settings';
    cache()->forget($cacheKey);
    // dd(\App\Core\Models\Setting::pluck('value', 'key')->toArray());

    $settings = Cache::rememberForever($cacheKey, function () {

        // Get all settings as a collection and convert to array
        $all_settings = Setting::pluck('value', 'key')->toArray();
        // Handle logo and icon URLs
        foreach (['logo', 'icon'] as $imageKey) {
            if (isset($all_settings[$imageKey]) && ! empty($all_settings[$imageKey])) {
                // Generate URL using asset helper for storage paths
                $all_settings[$imageKey] = asset('storage/'.$all_settings[$imageKey]);
            } else {
                $all_settings[$imageKey] = null;
            }
        }

        // Handle locales (JSON to array)
        $all_settings['locales'] = isset($all_settings['locales'])
            ? (json_decode($all_settings['locales'], true) ?: [])
            : [];

        // Handle app_tags (JSON to comma-separated string)
        $tags = isset($all_settings['app_tags'])
            ? (json_decode($all_settings['app_tags'], true) ?: [])
            : [];

        $all_settings['app_tags'] = is_array($tags) ? implode(', ', $tags) : '';

        // Handle currency - check if currencies table exists
        if (Schema::hasTable('currencies')) {
            $currency = Currency::find($all_settings['currency'] ?? 1);
            $all_settings['currency_symbol'] = $currency->symbol ?? '$';
            $all_settings['currency_code'] = $currency->code ?? 'USD';
        } else {
            // Default values when currencies table doesn't exist
            $all_settings['currency_symbol'] = '$';
            $all_settings['currency_code'] = 'USD';
        }

        return $all_settings;
    });

    if (is_null($key)) {
        return $settings;
    }

    $localeKey = $key.'_'.app()->getLocale();

    // Return the localized version if it exists, fall back to the default key,
    // and finally return the provided default value
    return $settings[$localeKey] ?? $settings[$key] ?? $default;
}

function storage($disk = 'public'): Filesystem
{
    return Storage::disk($disk);
}

function seo($tag = null, $default = null)
{
    if (is_null($tag)) {
        return new SEO;
    }

    $value = SEO::getTag($tag) ?? $default;

    try {
        if ($tag == 'keywords' && is_array($value)) {
            $value = implode(',', $value);
        }
    } catch (Exception) {
    }

    return $value;
}

if (! function_exists('hexToRgb')) {
    /**
     * Convert a hex color code to RGB format (000 000 000).
     */
    function hexToRgb(string $hex): string
    {
        // Remove the '#' if present
        $hex = ltrim($hex, '#');

        // Ensure the hex code is valid
        if (strlen($hex) !== 6) {
            return '0 0 0';
        }

        // Convert hex to RGB
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        // Format to ensure 3 digits with leading zeros
        return sprintf('%03d %03d %03d', $r, $g, $b);
    }
}

function appLocales()
{
    $locales = settings('locales', config('app.locales'));
    $current = app()->getLocale();

    $locales = array_values(array_diff($locales, [$current]));
    array_unshift($locales, $current);

    return $locales;
}

/**
 * Get master locales and prioritize current locale
 */
function masterLocales()
{
    $locales = masterSettings('master_locales', ['en', 'ar', 'ku']);
    $current = app()->getLocale();

    // Ensure locales is an array
    if (is_string($locales)) {
        $locales = json_decode($locales, true) ?: ['en'];
    }

    if (! is_array($locales)) {
        $locales = ['en'];
    }

    $locales = array_values(array_diff($locales, [$current]));
    array_unshift($locales, $current);

    return $locales;
}

function getLocaleDir(): string
{
    $locale = app()->getLocale();
    if ($locale === 'ar' || $locale === 'ku') {
        return 'rtl';
    }

    return 'ltr';
}

/**
 * Get master locale direction based on current locale
 */
function getMasterLocaleDir(): string
{
    $locale = app()->getLocale();

    if ($locale === 'ar' || $locale === 'ku') {
        return 'rtl';
    }

    return 'ltr';
}

if (! function_exists('money')) {
    /**
     * Format a number as a currency string.
     *
     * @param  float|int  $amount  The amount to format.
     * @param  string|null  $currencyCode  The currency code (e.g., 'USD', 'EUR'). If null, uses settings.
     * @param  int  $decimals  The number of decimal places.
     * @param  string  $decimalSeparator  The separator for the decimal point.
     * @param  string  $thousandsSeparator  The thousands separator.
     * @return string The formatted currency string.
     */
    function money(float|int $amount, ?string $currencyCode = null, int $decimals = 2, string $decimalSeparator = '.', string $thousandsSeparator = ','): string
    {
        $currencySymbol = '';
        if ($currencyCode === null) {
            $currencyCode = settings('currency_code', 'USD');
        }

        // You can extend this to get symbols based on currencyCode from settings or a config file
        switch (strtoupper($currencyCode)) {
            case 'USD':
                $currencySymbol = '$';
                break;
            case 'EUR':
                $currencySymbol = '€';
                break;
            case 'GBP':
                $currencySymbol = '£';
                break;
                // Add more currency cases as needed
            default:
                $currencySymbol = strtoupper($currencyCode).' ';
        }

        $formattedAmount = number_format($amount, $decimals, $decimalSeparator, $thousandsSeparator);

        // Example: Place symbol before or after based on locale/currency convention if needed
        // For simplicity, this example places it before.
        return $currencySymbol.$formattedAmount;
    }
}

function setUpEmailInfo($email)
{
    $customer = User::where('email', $email)->first();
    if ($customer) {
        $nationality = strtoupper($customer->nationality);
        $language = config('countries.languages.'.$nationality, config('app.fallback_locale', 'en'));
        context()->add('email_language', $language);
        $direction = in_array($language, config('countries.rtl_languages')) ? 'rtl' : 'ltr';
        context()->add('email_direction', $direction);
    }
}

function emailTranslation($key)
{
    $language = context()->get('email_language', 'en');

    return __($key, [], $language);
}

function getCurrentSubdomain(): ?string
{
    $host = request()->getHost();
    $subdomain = explode('.', $host);

    if (count($subdomain) > 2) {
        return $subdomain[0];
    }

    return null;
}

// Get current facility
function getCurrentFacility()
{
    try {
        // Skip if facilities table doesn't exist (during migrations)
        if (! Schema::hasTable('facilities')) {
            return null;
        }

        $subdomain = getCurrentSubdomain();

        return Facility::where('subdomain', $subdomain)->first();
    } catch (Exception $e) {
        // Return null if any database error occurs
        return null;
    }
}

// Get current facility id
function getCurrentFacilityId()
{
    try {
        // Skip if facilities table doesn't exist (during migrations)
        if (! Schema::hasTable('facilities')) {
            return null;
        }

        $subdomain = getCurrentSubdomain();

        return Facility::where('subdomain', $subdomain)->first()?->id ?? null;
    } catch (Exception $e) {
        // Return null if any database error occurs
        return null;
    }
}

/**
 * Get master settings from the master database
 */
function masterSettings(?string $key = null, $default = null)
{
    $cacheKey = 'master_settings';

    if (! Cache::has($cacheKey)) {
        loadMasterSettings();
    }

    $settings = Cache::get($cacheKey, []);

    if (is_null($key)) {
        return $settings;
    }

    $localeKey = $key.'_'.app()->getLocale();

    // Return the localized version if it exists, fall back to the default key,
    // and finally return the provided default value
    return $settings[$localeKey] ?? $settings[$key] ?? $default;
}

/**
 * Load master settings and cache them
 */
function loadMasterSettings(): void
{
    // Store current tenant connection if we're on one
    $wasOnTenant = TenantDatabaseService::isOnTenantConnection();
    $currentTenantConnection = TenantDatabaseService::getCurrentTenantConnection();

    try {
        // Switch to master database connection
        if ($wasOnTenant) {
            TenantDatabaseService::switchToMaster();
        }

        // Check if settings table exists in master database
        if (Schema::hasTable('settings')) {
            $cacheKey = 'master_settings';

            // Get all master settings from master database
            $all_settings = Setting::pluck('value', 'key')->toArray();

            // Handle logo and icon URLs for master panel
            $logoKeys = ['master_logo', 'master_favicon', 'master_login_image'];
            foreach ($logoKeys as $imageKey) {
                if (isset($all_settings[$imageKey]) && ! empty($all_settings[$imageKey])) {
                    // Generate URL using asset helper for storage paths
                    $all_settings[$imageKey] = asset('storage/'.$all_settings[$imageKey]);
                } else {
                    $all_settings[$imageKey] = null;
                }
            }

            Cache::put($cacheKey, $all_settings, now()->addDay());
        }
    } finally {
        // Restore tenant connection if we were on one
        if ($wasOnTenant && $currentTenantConnection) {
            Config::set('database.default', $currentTenantConnection);
            DB::setDefaultConnection($currentTenantConnection);
        }
    }
}
