<?php

namespace Modules\Core\Console\Commands;

use Modules\System\Actions\Facility\SeedFacilityDataAction;
use Modules\Master\Entities\Facility;
use Modules\Core\Services\TenantDatabaseService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class TenantDatabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:database 
                            {action : The action to perform (create, migrate, seed, drop, list)}
                            {--facility= : Facility ID (required for create, migrate, seed, drop)}
                            {--force : Force the operation without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage tenant databases';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $action = $this->argument('action');
        $facilityId = $this->option('facility');

        switch ($action) {
            case 'create':
                return $this->createTenantDatabase($facilityId);
            case 'migrate':
                return $this->migrateTenantDatabase($facilityId);
            case 'seed':
                return $this->seedTenantDatabase($facilityId);
            case 'drop':
                return $this->dropTenantDatabase($facilityId);
            case 'list':
                return $this->listTenantDatabases();
            default:
                $this->error("Unknown action: {$action}");

                return 1;
        }
    }

    private function createTenantDatabase(?string $facilityId): int
    {
        if (! $facilityId) {
            $this->error('Facility ID is required for create action');

            return 1;
        }

        $facility = Facility::find($facilityId);
        if (! $facility) {
            $this->error("Facility with ID {$facilityId} not found");

            return 1;
        }

        try {
            $this->info("Creating tenant database for facility: {$facility->name}");

            TenantDatabaseService::createTenantDatabase($facility);

            $databaseName = TenantDatabaseService::getTenantDatabaseName($facility);
            $this->info("Successfully created tenant database: {$databaseName}");

            return 0;
        } catch (Exception $e) {
            $this->error("Failed to create tenant database: {$e->getMessage()}");

            return 1;
        }
    }

    private function migrateTenantDatabase(?string $facilityId): int
    {
        if (! $facilityId) {
            $this->error('Facility ID is required for migrate action');

            return 1;
        }

        $facility = Facility::find($facilityId);
        if (! $facility) {
            $this->error("Facility with ID {$facilityId} not found");

            return 1;
        }

        try {
            $this->info("Running migrations for facility: {$facility->name}");

            $connectionName = TenantDatabaseService::getTenantConnectionName($facility);

            // Ensure database exists
            TenantDatabaseService::createTenantDatabase($facility);

            // Configure the tenant connection
            TenantDatabaseService::switchToTenant($facility);

            // Run migrations
            Artisan::call('migrate', [
                '--database' => $connectionName,
                '--force' => $this->option('force'),
            ]);

            // Switch back to default
            TenantDatabaseService::switchToMaster();

            $this->info('Successfully migrated tenant database');
            $this->line(Artisan::output());

            return 0;
        } catch (Exception $e) {
            TenantDatabaseService::switchToMaster();
            $this->error("Failed to migrate tenant database: {$e->getMessage()}");

            return 1;
        }
    }

    private function seedTenantDatabase(?string $facilityId): int
    {
        if (! $facilityId) {
            $this->error('Facility ID is required for seed action');

            return 1;
        }

        $facility = Facility::find($facilityId);
        if (! $facility) {
            $this->error("Facility with ID {$facilityId} not found");

            return 1;
        }

        try {
            $this->info("Seeding tenant database for facility: {$facility->name}");

            $action = new SeedFacilityDataAction;
            $action->execute($facility);

            $this->info('Successfully seeded tenant database');

            return 0;
        } catch (Exception $e) {
            $this->error("Failed to seed tenant database: {$e->getMessage()}");

            return 1;
        }
    }

    private function dropTenantDatabase(?string $facilityId): int
    {
        if (! $facilityId) {
            $this->error('Facility ID is required for drop action');

            return 1;
        }

        $facility = Facility::find($facilityId);
        if (! $facility) {
            $this->error("Facility with ID {$facilityId} not found");

            return 1;
        }

        $databaseName = TenantDatabaseService::getTenantDatabaseName($facility);

        if (! $this->option('force')) {
            if (! $this->confirm("Are you sure you want to drop the tenant database '{$databaseName}' for facility '{$facility->name}'?")) {
                $this->info('Operation cancelled');

                return 0;
            }
        }

        try {
            $this->info("Dropping tenant database: {$databaseName}");

            TenantDatabaseService::dropTenantDatabase($facility);

            $this->info('Successfully dropped tenant database');

            return 0;
        } catch (Exception $e) {
            $this->error("Failed to drop tenant database: {$e->getMessage()}");

            return 1;
        }
    }

    private function listTenantDatabases(): int
    {
        $facilities = Facility::all();

        if ($facilities->isEmpty()) {
            $this->info('No facilities found');

            return 0;
        }

        $this->info('Tenant Databases:');
        $this->line('');

        $headers = ['Facility ID', 'Facility Name', 'Database Name', 'Exists', 'Connection Test'];
        $rows = [];

        foreach ($facilities as $facility) {
            $databaseName = TenantDatabaseService::getTenantDatabaseName($facility);
            $exists = TenantDatabaseService::tenantDatabaseExists($facility) ? '✓' : '✗';
            $connectionTest = TenantDatabaseService::testTenantConnection($facility) ? '✓' : '✗';

            $rows[] = [
                $facility->id,
                $facility->name['en'] ?? $facility->name,
                $databaseName,
                $exists,
                $connectionTest,
            ];
        }

        $this->table($headers, $rows);

        return 0;
    }
}
