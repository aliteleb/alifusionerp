<?php

namespace Modules\Core\Services;

use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\Master\Entities\Facility;
use PDO;

class TenantDatabaseService
{
    public const MASTER_CONNECTION = 'master';

    public const TENANT_CONNECTION = 'tenant';

    /**
     * The facility currently bound to the application context.
     */
    protected static ?Facility $currentFacility = null;

    public static function setCurrentFacility(?Facility $facility): void
    {
        self::$currentFacility = $facility;
    }

    public static function getCurrentFacility(): ?Facility
    {
        return self::$currentFacility;
    }

    /**
     * Get the tenant database name for a facility
     */
    public static function getTenantDatabaseName(Facility $facility): string
    {
        if (! empty($facility->database_name)) {
            return $facility->database_name;
        }

        $prefix = self::getTenantDatabasePrefix();
        $safeSubdomain = self::getSafeSubdomain($facility);

        return $prefix.$safeSubdomain;
    }

    /**
     * Get the tenant database prefix from configuration
     */
    public static function getTenantDatabasePrefix(): string
    {
        return config('tenant.database.prefix', 'dws_tenant_');
    }

    /**
     * Validate tenant database name against security rules
     */
    public static function validateTenantDatabaseName(string $name): bool
    {
        // PostgreSQL: Only allow letters, numbers, and underscores
        $pattern = '/^[a-zA-Z0-9_]+$/';

        return preg_match($pattern, $name) === 1;
    }

    /**
     * Get safe subdomain for database naming
     */
    public static function getSafeSubdomain(Facility $facility): string
    {
        $subdomain = $facility->subdomain ?? Str::slug($facility->name['en'] ?? $facility->name);

        // Sanitize subdomain to ensure it's safe for database naming
        $sanitized = preg_replace('/[^a-zA-Z0-9_-]/', '_', $subdomain);
        $sanitized = trim($sanitized, '_-');

        // PostgreSQL doesn't allow hyphens in database names, replace with underscores
        $driver = Config::get('database.default');
        if ($driver === 'pgsql') {
            $sanitized = str_replace('-', '_', $sanitized);
        }

        return strtolower($sanitized);
    }

    /**
     * Get facility by subdomain
     */
    public static function getFacilityBySubdomain(string $subdomain): ?Facility
    {
        return Facility::where('subdomain', $subdomain)->first();
    }

    /**
     * Detect and switch to tenant based on current domain/subdomain
     */
    public static function detectAndSwitchTenant(?string $subdomain = null): ?Facility
    {
        if (! $subdomain) {
            $subdomain = self::getCurrentSubdomain();
        }

        if (! $subdomain) {
            return null;
        }

        $facility = self::getFacilityBySubdomain($subdomain);

        if ($facility && self::tenantDatabaseExists($facility)) {
            self::connectToFacility($facility);

            return $facility;
        }

        return null;
    }

    /**
     * Get current subdomain from request
     */
    public static function getCurrentSubdomain(): ?string
    {
        if (app()->runningInConsole()) {
            return null;
        }

        $host = request()->getHost();
        $parts = explode('.', $host);

        // If we have more than 2 parts (subdomain.domain.tld), return the first part as subdomain
        if (count($parts) > 2) {
            return $parts[0];
        }

        return null;
    }

    /**
     * Check if tenant database exists
     */
    public static function tenantDatabaseExists(Facility $facility): bool
    {
        $databaseName = self::getTenantDatabaseName($facility);

        try {
            $databases = DB::connection(self::MASTER_CONNECTION)
                ->select('SELECT 1 FROM pg_database WHERE datname = ?', [$databaseName]);

            return ! empty($databases);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Create tenant database if it doesn't exist
     */
    public static function createTenantDatabase(Facility $facility): void
    {
        $databaseName = self::getTenantDatabaseName($facility);

        if (! self::tenantDatabaseExists($facility)) {
            // PostgreSQL: Use a completely separate connection to avoid transaction conflicts
            // Create a new PDO connection outside of any Laravel transaction context
            $config = Config::get('database.connections.'.self::MASTER_CONNECTION);
            $dsn = "pgsql:host={$config['host']};port={$config['port']};dbname={$config['database']}";
            $pdo = new PDO($dsn, $config['username'], $config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);

            // Execute CREATE DATABASE outside of any transaction
            $pdo->exec("CREATE DATABASE {$databaseName}");
        }
    }

    /**
     * Drop tenant database
     */
    public static function dropTenantDatabase(Facility $facility): void
    {
        $databaseName = $facility->database_name;

        // First, terminate any active connections to the database
        self::terminateActiveConnections($databaseName);

        // PostgreSQL: Use a completely separate connection to avoid transaction conflicts
        // Create a new PDO connection outside of any Laravel transaction context
        $config = Config::get('database.connections.'.self::MASTER_CONNECTION);
        $dsn = "pgsql:host={$config['host']};port={$config['port']};dbname={$config['database']}";
        $pdo = new PDO($dsn, $config['username'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);

        // Execute DROP DATABASE outside of any transaction
        $pdo->exec("DROP DATABASE IF EXISTS {$databaseName}");
    }

    /**
     * Terminate active connections to a PostgreSQL database before dropping it
     */
    private static function terminateActiveConnections(string $databaseName): void
    {
        try {
            // Use the master PostgreSQL connection (not the tenant connection)
            $mainConnection = DB::connection(self::MASTER_CONNECTION);

            Log::info('Terminating active connections to database', [
                'database' => $databaseName,
            ]);

            // Terminate backend connections to the database
            $mainConnection->statement('
                SELECT pg_terminate_backend(pg_stat_activity.pid)
                FROM pg_stat_activity
                WHERE pg_stat_activity.datname = ?
                AND pid <> pg_backend_pid()
            ', [$databaseName]);
        } catch (Exception $e) {
            Log::warning('Failed to terminate active connections', [
                'database' => $databaseName,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Switch to tenant database connection
     */
    public static function configureTenantConnection(Facility $facility): void
    {
        $tenantConfig = Config::get('database.connections.'.self::TENANT_CONNECTION, []);
        $tenantConfig['database'] = $facility->database_name;
        Config::set('database.connections.'.self::TENANT_CONNECTION, $tenantConfig);
    }

    /**
     * Switch back to the default (master) database connection
     */
    public static function switchToMaster(): void
    {
        self::setCurrentFacility(null);

        // Set the default connection back to the master connection
        Config::set('database.default', self::MASTER_CONNECTION);
        DB::setDefaultConnection(self::MASTER_CONNECTION);

        self::setCurrentFacility(null);

        // Reconnect to the master database
        DB::purge(self::MASTER_CONNECTION);
        DB::reconnect(self::MASTER_CONNECTION);
    }

    /**
     * Check if we're currently on a tenant connection
     */
    public static function isOnTenantConnection(): bool
    {
        return Config::get('database.default') === self::TENANT_CONNECTION;
    }

    /**
     * Test if we can connect to a specific facility's tenant database
     */
    public static function testTenantConnection(Facility $facility): bool
    {
        try {
            // Store current connection state
            $wasOnTenant = self::isOnTenantConnection();
            $previousFacility = self::getCurrentFacility();

            // Ensure we're using the master database first
            self::switchToMaster();

            // Try to switch to the tenant database
            self::connectToFacility($facility);

            // Test the connection by running a simple query
            DB::connection(self::TENANT_CONNECTION)->select('SELECT 1');

            // Switch back to original connection
            if ($wasOnTenant && $previousFacility) {
                self::connectToFacility($previousFacility);
            } else {
                self::switchToMaster();
            }

            return true;
        } catch (Exception $e) {
            // Always try to switch back to default on error
            try {
                self::switchToMaster();
            } catch (Exception $switchException) {
                // Ignore switch errors in error handling
            }

            return false;
        }
    }

    /**
     * Connect to a specific facility's tenant database
     */
    public static function connectToFacility(Facility $facility): void
    {
        self::setCurrentFacility($facility);

        self::configureTenantConnection($facility);

        // Set the default connection to the tenant connection
        Config::set('database.default', self::TENANT_CONNECTION);
        DB::setDefaultConnection(self::TENANT_CONNECTION);

        // Reconnect to the tenant database
        DB::purge(self::TENANT_CONNECTION);
        DB::reconnect(self::TENANT_CONNECTION);
    }

    /**
     * Switch to tenant database connection
     */
    public static function connectToCurrentFacility(): void
    {
        $subdomain = self::getCurrentSubdomainFromRequest();

        if (! $subdomain) {
            return;
        }

        $facility = Facility::where('subdomain', $subdomain)->first();

        if (! $facility) {
            // Throw exception
            throw new Exception('Organization not found with subdomain: '.$subdomain);
        }

        self::setCurrentFacility($facility);
        // Switch to the tenant database
        self::connectToFacility($facility);
    }

    /**
     * Get current subdomain
     */
    public static function getCurrentSubdomainFromRequest(): ?string
    {
        if (app()->runningInConsole()) {
            return null;
        }

        $host = request()->getHost();
        $parts = explode('.', $host);

        if (count($parts) > 2) {
            return $parts[0];
        }

        return null;
    }
}
