<?php

namespace App\Console\Commands;

use Modules\System\Actions\Facility\SeedFacilityDataAction;
use Modules\Master\Entities\Facility;
use Modules\Core\Services\TenantDatabaseService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
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
    protected $description = 'Run migrations for tenant databases (all facilities or specific facility, supports rollback)';

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

            // Build command options
            $options = [
                '--database' => $connectionName,
                '--path' => 'database/migrations/tenant',
            ];

            $command = 'migrate';

            if ($this->option('rollback')) {
                $command = 'migrate:rollback';

                // Add step option if provided for rollback
                if ($this->option('step')) {
                    $options['--step'] = $this->option('step');
                }

                // Confirmation for rollback (unless forced)
                if (! $this->option('force')) {
                    $stepText = $this->option('step') ? $this->option('step').' migration(s)' : 'last migration batch';
                    if (! $this->confirm("Rollback {$stepText} for facility '{$facility->name}'?")) {
                        $this->line("Skipping rollback for {$facility->name}");
                        TenantDatabaseService::switchToMaster();

                        return self::SUCCESS;
                    }
                }
            } else {
                // Regular migration options
                if ($this->option('fresh')) {
                    $command = 'migrate:fresh';
                }

                // DON'T add --seed to the migrate command
                // We'll handle tenant seeding separately after migration
            }

            if ($this->option('force')) {
                $options['--force'] = true;
            }

            // Run the migration command (without --seed)
            Artisan::call($command, $options);

            $migrationOutput = trim(Artisan::output());
            if ($migrationOutput !== '') {
                $this->line($migrationOutput);
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
}
