<?php

namespace Modules\Core\Console\Commands;

use Modules\System\Actions\Database\TenantDatabaseActions;
use Modules\Master\Entities\Facility;
use Modules\Core\Services\TenantDatabaseService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

class MasterMigrateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'master:migrate {--fresh} {--seed} {--rollback} {--step=} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run migrations for master/system database only (supports rollback)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Handle rollback operation
        if ($this->option('rollback')) {
            return $this->handleRollback();
        }

        $this->info('🔄 Running master/system migrations...');

        // Build command options
        $options = [];

        $command = 'migrate';
        if ($this->option('fresh')) {
            $this->line('⚠️  Fresh migration requested - this will drop all tables!');

            if (! $this->option('force') && ! $this->confirm('This will delete ALL master data and tenant databases. Are you sure?')) {
                $this->info('Migration cancelled.');

                return self::SUCCESS;
            }

            $command = 'migrate:fresh';

            if (Schema::hasTable('facilities')) {
                $this->info('🗑️  Cleaning up existing tenant databases...');
                // Delete all existing records and databases
                $facilities = Facility::all();
                $count = $facilities->count();

                if ($count > 0) {
                    $this->line("Found {$count} facilities to clean up:");

                    $facilities->each(function ($facility) {
                        $this->line("  • Dropping database for: {$facility->name}");
                        TenantDatabaseService::dropTenantDatabase($facility);
                        $facility->forceDelete();
                    });

                    $this->info("✅ Cleaned up {$count} tenant databases");
                } else {
                    $this->line('No existing facilities found to clean up.');
                }
            }
        }

        if ($this->option('seed')) {
            $options['--seed'] = true;
        }

        if ($this->option('force')) {
            $options['--force'] = true;
        }

        // Run migrations from master directory only
        $options['--path'] = 'database/migrations/master';

        try {
            $this->line('📊 Running migrations from: database/migrations/master');

            Artisan::call($command, $options);

            $output = Artisan::output();
            if (trim($output)) {
                $this->line($output);
            }

            $this->info('✅ Master migrations completed successfully!');

            if ($this->option('seed')) {
                $this->info('🌱 Database seeding completed!');

                // Automatically create and seed tenant databases for all facilities
                $this->createAndSeedTenantDatabases();
            }

            return self::SUCCESS;
        } catch (Exception $e) {
            $this->error('❌ Master migration failed: '.$e->getMessage());

            return self::FAILURE;
        }
    }

    /**
     * Handle rollback operations for master database.
     */
    private function handleRollback(): int
    {
        $this->info('🔄 Rolling back master/system migrations...');

        $options = [
            '--path' => 'database/migrations/master',
        ];

        // Add step option if provided
        if ($this->option('step')) {
            $options['--step'] = $this->option('step');
            $this->line("📉 Rolling back {$this->option('step')} migration(s)");
        } else {
            $this->line('📉 Rolling back last migration batch');
        }

        if ($this->option('force')) {
            $options['--force'] = true;
        } else {
            $stepText = $this->option('step') ? $this->option('step').' migration(s)' : 'last migration batch';
            if (! $this->confirm("Are you sure you want to rollback {$stepText} from master database?")) {
                $this->info('Rollback cancelled.');

                return self::SUCCESS;
            }
        }

        try {
            Artisan::call('migrate:rollback', $options);

            $output = Artisan::output();
            if (trim($output)) {
                $this->line($output);
            }

            $this->info('✅ Master migrations rollback completed successfully!');

            return self::SUCCESS;
        } catch (Exception $e) {
            $this->error('❌ Master migration rollback failed: '.$e->getMessage());

            return self::FAILURE;
        }
    }

    /**
     * Create and seed tenant databases for all facilities
     */
    private function createAndSeedTenantDatabases(): void
    {
        $this->info('Creating and seeding tenant databases...');

        $facilities = Facility::all();

        if ($facilities->isEmpty()) {
            $this->line('No facilities found. Skipping tenant database creation.');

            return;
        }

        // Create tenant database actions instance
        $tenantActions = new TenantDatabaseActions(new \App\Core\Services\MigrationStatusService);

        foreach ($facilities as $facility) {
            try {
                $this->line("Creating tenant database for facility: {$facility->name}");

                // Create tenant database
                TenantDatabaseService::createTenantDatabase($facility);

                // Run tenant migrations
                $this->line("Running migrations for facility: {$facility->name}");
                $migrationResult = $tenantActions->runTenantMigration($facility->id);
                if ($migrationResult['status'] === 'error') {
                    throw new Exception($migrationResult['message']);
                }
                $this->line($migrationResult['output']);

                // Seed tenant database
                $this->line("Seeding database for facility: {$facility->name}");
                $tenantActions->seedTenantDatabase($facility->id);

                $this->info("✅ Successfully created and seeded tenant database for: {$facility->name}");
            } catch (Exception $e) {
                $this->error("❌ Failed to create/seed tenant database for {$facility->name}: {$e->getMessage()}");
            }
        }

        $this->info('✅ All tenant databases created and seeded successfully!');
    }
}
