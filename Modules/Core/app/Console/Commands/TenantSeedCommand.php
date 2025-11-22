<?php

namespace Modules\Core\Console\Commands;

use Modules\System\Actions\Facility\SeedFacilityDataAction;
use Modules\System\Actions\Facility\Seeding\SeedBranchDataAction;
use Modules\System\Actions\Facility\Seeding\SeedClientDataAction;
use Modules\System\Actions\Facility\Seeding\SeedClientGroupDataAction;
use Modules\System\Actions\Facility\Seeding\SeedDepartmentDataAction;
use Modules\System\Actions\Facility\Seeding\SeedProjectCategoryDataAction;
use Modules\System\Actions\Facility\Seeding\SeedProjectDataAction;
use Modules\System\Actions\Facility\Seeding\SeedReferenceDataAction;
use Modules\System\Actions\Facility\Seeding\SeedRolesAndPermissionsAction;
use Modules\System\Actions\Facility\Seeding\SeedSettingsDataAction;
use Modules\System\Actions\Facility\Seeding\SeedTicketDataAction;
use Modules\System\Actions\Facility\Seeding\SeedUserDataAction;
use Modules\Master\Entities\Facility;
use Modules\Core\Services\TenantDatabaseService;
use Exception;
use Illuminate\Console\Command;
use ReflectionClass;

class TenantSeedCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:seed 
                            {--facility= : Specific facility subdomain or ID to seed (if not provided, seeds all facilities)}
                            {--class= : Specific seeder class to run (reference, branches, clients, customers, customer-groups, departments, project-categories, projects, roles, settings, tickets, users)}
                            {--fresh : Truncate tables before seeding (use with caution)}
                            {--force : Force the operation without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run seeders for tenant databases (all facilities or specific facility)';

    /**
     * Available seeder classes mapping
     */
    private array $availableSeeders = [
        'reference' => SeedReferenceDataAction::class,
        'branches' => SeedBranchDataAction::class,
        'clients' => SeedClientDataAction::class,
        'client-groups' => SeedClientGroupDataAction::class,
        'departments' => SeedDepartmentDataAction::class,
        'project-categories' => SeedProjectCategoryDataAction::class,
        'projects' => SeedProjectDataAction::class,
        'roles' => SeedRolesAndPermissionsAction::class,
        'settings' => SeedSettingsDataAction::class,
        'tickets' => SeedTicketDataAction::class,
        'users' => SeedUserDataAction::class,
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $facilityIdentifier = $this->option('facility');
        $seederClass = $this->option('class');

        // Validate seeder class if provided
        if ($seederClass && ! array_key_exists($seederClass, $this->availableSeeders)) {
            $this->error("Invalid seeder class '{$seederClass}'. Available options: ".implode(', ', array_keys($this->availableSeeders)));

            return self::FAILURE;
        }

        if ($facilityIdentifier) {
            return $this->seedSingleFacility($facilityIdentifier, $seederClass);
        } else {
            return $this->seedAllFacilities($seederClass);
        }
    }

    private function seedSingleFacility(string $facilityIdentifier, ?string $seederClass = null): int
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

        if ($seederClass) {
            $this->info("Running '{$seederClass}' seeder for facility: {$facility->name} (subdomain: {$facility->subdomain})");
        } else {
            $this->info("Running all seeders for facility: {$facility->name} (subdomain: {$facility->subdomain})");
        }

        return $this->runSeedingForFacility($facility, $seederClass);
    }

    private function seedAllFacilities(?string $seederClass = null): int
    {
        // Ensure we're using the master database to query facilities
        TenantDatabaseService::switchToMaster();

        $facilities = Facility::all();

        if ($facilities->isEmpty()) {
            $this->info('No facilities found');

            return self::SUCCESS;
        }

        if ($seederClass) {
            $this->info("Running '{$seederClass}' seeder for all {$facilities->count()} facilities...");
        } else {
            $this->info("Running all seeders for all {$facilities->count()} facilities...");
        }

        $errors = 0;

        foreach ($facilities as $facility) {
            if ($seederClass) {
                $this->line("\n🌱 Seeding '{$seederClass}' for facility: {$facility->name} (subdomain: {$facility->subdomain}, ID: {$facility->id})");
            } else {
                $this->line("\n🌱 Seeding facility: {$facility->name} (subdomain: {$facility->subdomain}, ID: {$facility->id})");
            }

            $result = $this->runSeedingForFacility($facility, $seederClass);
            if ($result !== self::SUCCESS) {
                $errors++;
            }
        }

        if ($errors > 0) {
            $this->error("\n❌ {$errors} facility seedings failed");

            return self::FAILURE;
        } else {
            $this->info("\n✅ All facility seedings completed successfully!");

            return self::SUCCESS;
        }
    }

    private function runSeedingForFacility(Facility $facility, ?string $seederClass = null): int
    {
        try {
            // Connect to the tenant database
            TenantDatabaseService::connectToFacility($facility);

            // Warning for fresh option
            if ($this->option('fresh') && ! $this->option('force')) {
                if (! $this->confirm("⚠️  This will truncate existing data in {$facility->name}. Are you sure?")) {
                    $this->line("Skipping seeding for {$facility->name}");
                    TenantDatabaseService::switchToMaster();

                    return self::SUCCESS;
                }
            }

            if ($seederClass) {
                // Run specific seeder
                $this->runSpecificSeeder($facility, $seederClass);
            } else {
                // Run full seeding suite
                $this->runFullSeeding($facility);
            }

            $this->info("✅ {$facility->name}: Seeding completed successfully!");

            // Switch back to default database
            TenantDatabaseService::switchToMaster();

            return self::SUCCESS;

        } catch (Exception $e) {
            // Always try to switch back to default on error
            try {
                TenantDatabaseService::switchToMaster();
            } catch (Exception $switchException) {
                // Ignore switch errors in error handling
            }

            $this->error("❌ {$facility->name}: Seeding failed: {$e->getMessage()}");

            return self::FAILURE;
        }
    }

    private function runSpecificSeeder(Facility $facility, string $seederClass): void
    {
        $seederClassName = $this->availableSeeders[$seederClass];
        $seeder = new $seederClassName;

        $this->info("🌱 {$facility->name}: Running {$seederClass} seeder...");

        // Use reflection to determine if the execute method requires a Facility parameter
        if (method_exists($seeder, 'execute')) {
            $requiresFacility = $this->seederRequiresFacilityParameter($seederClassName);

            if ($requiresFacility) {
                $seeder->execute($facility);
            } else {
                $seeder->execute();
            }
        } else {
            throw new Exception("Seeder class {$seederClassName} does not have execute method");
        }

        $this->info("✅ {$facility->name}: {$seederClass} seeder completed!");
    }

    /**
     * Check if the seeder's execute method requires a Facility parameter using reflection
     */
    private function seederRequiresFacilityParameter(string $seederClassName): bool
    {
        try {
            $reflection = new ReflectionClass($seederClassName);
            $executeMethod = $reflection->getMethod('execute');
            $parameters = $executeMethod->getParameters();

            // Check if the first parameter is of type Facility
            if (count($parameters) > 0) {
                $firstParameter = $parameters[0];
                $parameterType = $firstParameter->getType();

                if ($parameterType && $parameterType->getName() === Facility::class) {
                    return true;
                }
            }

            return false;
        } catch (Exception $e) {
            // If reflection fails, log the error and default to no facility parameter
            $this->warn("Could not determine parameter requirements for {$seederClassName}: {$e->getMessage()}");

            return false;
        }
    }

    private function runFullSeeding(Facility $facility): void
    {
        $this->info("🌱 {$facility->name}: Running complete tenant seeding...");

        $seedAction = new SeedFacilityDataAction;
        $seedAction->execute($facility);

        $this->info("✅ {$facility->name}: Complete seeding finished!");
    }
}
