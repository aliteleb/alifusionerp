<?php

namespace Modules\Core\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Modules\Core\Services\TenantDatabaseService;
use Modules\Master\Entities\Facility;
use Modules\System\Actions\Facility\SeedFacilityDataAction;
use stdClass;

class TenantMigrateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:migrate 
                            {--facility= : Specific facility subdomain or ID to migrate (if not provided, migrates all facilities)}
                            {--module=* : Specific module(s) to migrate (if not provided, migrates all modules except Master)}
                            {--fresh : Drop all tables and re-run all migrations}
                            {--seed : Run database seeders after migration}
                            {--rollback : Rollback migrations instead of running them}
                            {--step= : Number of migrations to rollback (only with --rollback)}
                            {--force : Force the operation without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run migrations for tenant databases (all facilities or specific facility, supports rollback). By default runs migrations from all modules except Master.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Check for conflicting options
        if ($this->option('rollback') && ($this->option('fresh') || $this->option('seed'))) {
            $this->error('Rollback cannot be used with --fresh or --seed options');

            return self::FAILURE;
        }

        $facilityIdentifier = $this->option('facility');

        if ($facilityIdentifier) {
            return $this->migrateSingleFacility($facilityIdentifier);
        } else {
            return $this->migrateAllFacilities();
        }
    }

    private function migrateSingleFacility(string $facilityIdentifier): int
    {
        // Ensure we're using the master database to query facilities
        TenantDatabaseService::switchToMaster();

        // Try to find facility by subdomain first, then by ID
        $facility = Facility::where('subdomain', $facilityIdentifier)->first();

        if (! $facility && is_numeric($facilityIdentifier)) {
            $facility = Facility::find($facilityIdentifier);
        }

        if (! $facility) {
            $this->error("Facility with subdomain or ID '{$facilityIdentifier}' not found");

            return self::FAILURE;
        }

        if ($this->option('rollback')) {
            $this->info("Rolling back tenant migrations for facility: {$facility->name} (subdomain: {$facility->subdomain})");
        } else {
            $this->info("Running tenant migrations for facility: {$facility->name} (subdomain: {$facility->subdomain})");
        }

        return $this->runMigrationForFacility($facility);
    }

    private function migrateAllFacilities(): int
    {
        // Ensure we're using the master database to query facilities
        TenantDatabaseService::switchToMaster();

        $facilities = Facility::all();

        if ($facilities->isEmpty()) {
            $this->info('No facilities found');

            return self::SUCCESS;
        }

        if ($this->option('rollback')) {
            $this->info("Rolling back tenant migrations for all {$facilities->count()} facilities...");
        } else {
            $this->info("Running tenant migrations for all {$facilities->count()} facilities...");
        }

        $errors = 0;

        foreach ($facilities as $facility) {
            if ($this->option('rollback')) {
                $this->line("\n🔄 Rolling back facility: {$facility->name} (subdomain: {$facility->subdomain}, ID: {$facility->id})");
            } else {
                $this->line("\n🔄 Migrating facility: {$facility->name} (subdomain: {$facility->subdomain}, ID: {$facility->id})");
            }

            $result = $this->runMigrationForFacility($facility);
            if ($result !== self::SUCCESS) {
                $errors++;
            }
        }

        if ($errors > 0) {
            if ($this->option('rollback')) {
                $this->error("\n❌ {$errors} facility rollbacks failed");
            } else {
                $this->error("\n❌ {$errors} facility migrations failed");
            }

            return self::FAILURE;
        } else {
            if ($this->option('rollback')) {
                $this->info("\n✅ All facility rollbacks completed successfully!");
            } else {
                $this->info("\n✅ All facility migrations completed successfully!");
            }

            return self::SUCCESS;
        }
    }

    private function runMigrationForFacility(Facility $facility): int
    {
        try {
            $connectionName = TenantDatabaseService::TENANT_CONNECTION;

            // Ensure database exists (skip for rollback)
            if (! $this->option('rollback')) {
                // Switch to default temporarily to create database
                $wasOnTenant = TenantDatabaseService::isOnTenantConnection();
                if ($wasOnTenant) {
                    TenantDatabaseService::switchToMaster();
                }

                TenantDatabaseService::createTenantDatabase($facility);

                // Switch back if we were on tenant before
                if ($wasOnTenant) {
                    TenantDatabaseService::connectToFacility($facility);
                }
            }

            // Configure the tenant connection (ensure we're on the right tenant)
            TenantDatabaseService::connectToFacility($facility);

            // Get modules to migrate
            $modulesToMigrate = $this->getModulesToMigrate();

            if (empty($modulesToMigrate)) {
                $this->warn("No modules found to migrate for facility '{$facility->name}'");
                TenantDatabaseService::switchToMaster();

                return self::SUCCESS;
            }

            // Display which modules will be migrated
            $this->line('📦 Migrating modules: '.implode(', ', $modulesToMigrate));

            // Handle --fresh option (only for first module, drops all tables)
            if ($this->option('fresh') && ! $this->option('rollback')) {
                $firstModule = $modulesToMigrate[0];
                $firstModulePath = "Modules/{$firstModule}/database/migrations";

                if (is_dir(base_path($firstModulePath))) {
                    $this->line("  → Running fresh migration for module: {$firstModule} (this will drop all tables)");
                    $options = [
                        '--database' => $connectionName,
                        '--path' => $firstModulePath,
                        '--force' => $this->option('force') ?: true,
                    ];

                    Artisan::call('migrate:fresh', $options);
                    $output = trim(Artisan::output());
                    if ($output) {
                        $this->line("    ✓ {$firstModule}: {$output}");
                    }

                    // Remove first module from list since it's already migrated with fresh
                    array_shift($modulesToMigrate);
                }
            }

            // Run migrations for remaining modules (or all if not using --fresh)
            foreach ($modulesToMigrate as $moduleName) {
                $migrationPath = "Modules/{$moduleName}/database/migrations";

                // Check if migration directory exists
                if (! is_dir(base_path($migrationPath))) {
                    $this->warn("⚠️  Migration directory not found for module '{$moduleName}': {$migrationPath}");

                    continue;
                }

                $this->line("  → Running migrations for module: {$moduleName}");

                // Build command options
                $options = [
                    '--database' => $connectionName,
                    '--path' => $migrationPath,
                ];

                $command = 'migrate';

                if ($this->option('rollback')) {
                    $command = 'migrate:rollback';

                    // Add step option if provided for rollback
                    if ($this->option('step')) {
                        $options['--step'] = $this->option('step');
                    }

                    // Confirmation for rollback (unless forced) - only ask once for all modules
                    if (! $this->option('force') && $moduleName === $modulesToMigrate[0]) {
                        $stepText = $this->option('step') ? $this->option('step').' migration(s)' : 'last migration batch';
                        if (! $this->confirm("Rollback {$stepText} for facility '{$facility->name}'?")) {
                            $this->line("Skipping rollback for {$facility->name}");
                            TenantDatabaseService::switchToMaster();

                            return self::SUCCESS;
                        }
                    }
                }

                if ($this->option('force')) {
                    $options['--force'] = true;
                }

                // Run the migration command for this module
                try {
                    Artisan::call($command, $options);
                    $output = trim(Artisan::output());
                    if ($output) {
                        $this->line("    ✓ {$moduleName}: {$output}");
                    }
                } catch (Exception $e) {
                    $this->error("    ✗ {$moduleName}: {$e->getMessage()}");
                    throw $e;
                }
            }

            // Handle tenant seeding separately after successful migration
            if ($this->option('seed') && ! $this->option('rollback')) {
                $this->info("🌱 {$facility->name}: Running tenant-specific seeding...");

                try {
                    // Use our custom tenant seeding action
                    $seedAction = new SeedFacilityDataAction;
                    // Create a temporary facility-like object with just the data we need
                    $tempFacility = new stdClass;
                    $tempFacility->id = $facility->id;
                    $tempFacility->name = $facility->name;
                    $tempFacility->subdomain = $facility->subdomain;

                    // Call the specific tenant seeding method directly
                    $this->seedTenantDataOnly($facility);

                    $this->info("✅ {$facility->name}: Tenant seeding completed successfully!");
                } catch (Exception $seedException) {
                    $this->error("❌ {$facility->name}: Tenant seeding failed: {$seedException->getMessage()}");
                    // Don't fail the whole operation for seeding errors
                }
            }

            if ($this->option('rollback')) {
                $this->info("✅ {$facility->name}: Tenant rollback completed successfully!");
            } else {
                $this->info("✅ {$facility->name}: Tenant migrations completed successfully!");
            }

            // Always switch back to default after processing each facility
            // This ensures clean state for the next facility
            TenantDatabaseService::switchToMaster();

            return self::SUCCESS;
        } catch (Exception $e) {
            // Always try to switch back to default on error
            try {
                TenantDatabaseService::switchToMaster();
            } catch (Exception $switchException) {
                // Ignore switch errors in error handling
            }

            if ($this->option('rollback')) {
                $this->error("❌ {$facility->name}: Tenant rollback failed: {$e->getMessage()}");
            } else {
                $this->error("❌ {$facility->name}: Tenant migration failed: {$e->getMessage()}");
            }

            return self::FAILURE;
        }
    }

    /**
     * Seed tenant-specific data without running master seeders
     */
    private function seedTenantDataOnly(Facility $facility): void
    {
        $seedAction = new SeedFacilityDataAction;
        $this->info("🌱 {$facility->name}: Running tenant-specific seeding...");
        $seedAction->execute($facility);
        $this->info("✅ {$facility->name}: Tenant seeding completed successfully!");
    }

    /**
     * Get modules to migrate based on --module option or discover all modules except Master
     */
    private function getModulesToMigrate(): array
    {
        $selectedModules = $this->option('module');

        // If specific modules are selected, validate and return them
        if (! empty($selectedModules)) {
            $validModules = [];
            foreach ($selectedModules as $moduleName) {
                if ($this->moduleExists($moduleName) && $moduleName !== 'Master') {
                    $validModules[] = $moduleName;
                } else {
                    if ($moduleName === 'Master') {
                        $this->warn("⚠️  Master module is excluded from tenant migrations. Skipping '{$moduleName}'.");
                    } else {
                        $this->warn("⚠️  Module '{$moduleName}' not found. Skipping.");
                    }
                }
            }

            return $validModules;
        }

        // Otherwise, discover all modules except Master
        return $this->discoverTenantModules();
    }

    /**
     * Discover all modules that have tenant migrations (excluding Master)
     */
    private function discoverTenantModules(): array
    {
        $modulesPath = base_path('Modules');
        $modules = [];

        if (! is_dir($modulesPath)) {
            return $modules;
        }

        $directories = scandir($modulesPath);

        foreach ($directories as $directory) {
            // Skip . and .. and Master module
            if ($directory === '.' || $directory === '..' || $directory === 'Master') {
                continue;
            }

            $modulePath = $modulesPath.'/'.$directory;
            $migrationsPath = $modulePath.'/database/migrations';

            // Check if it's a directory and has migrations
            if (is_dir($modulePath) && is_dir($migrationsPath)) {
                $modules[] = $directory;
            }
        }

        return $modules;
    }

    /**
     * Check if a module exists
     */
    private function moduleExists(string $moduleName): bool
    {
        $modulePath = base_path('Modules/'.$moduleName);

        return is_dir($modulePath);
    }
}
