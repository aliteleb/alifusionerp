<?php

namespace Modules\System\Actions\Facility;

use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Modules\Core\Entities\Branch;
use Modules\Core\Entities\Country;
use Modules\Core\Entities\Currency;
use Modules\Core\Entities\Department;
use Modules\Core\Entities\Gender;
use Modules\Core\Entities\MaritalStatus;
use Modules\Core\Entities\Nationality;
use Modules\Core\Entities\Role;
use Modules\Core\Entities\User;
use Modules\Core\Services\TenantDatabaseService;
use Modules\Master\Entities\Facility;
use Modules\System\Actions\Facility\Seeding\SeedBranchDataAction;
use Modules\System\Actions\Facility\Seeding\SeedDepartmentDataAction;
use Modules\System\Actions\Facility\Seeding\SeedReferenceDataAction;
use Modules\System\Actions\Facility\Seeding\SeedRolesAndPermissionsAction;
use Modules\System\Actions\Facility\Seeding\SeedSettingsDataAction;
use Modules\System\Actions\Facility\Seeding\SeedUserDataAction;

class SeedFacilityDataAction
{
    /**
     * Execute the seeding action for a facility
     */
    public function execute(Facility $facility): void
    {
        Log::info('Starting seeding process for facility', ['facility_id' => $facility->id]);

        try {
            // Connect to the tenant database
            TenantDatabaseService::connectToFacility($facility);

            // Run tenant migrations first
            $connectionName = TenantDatabaseService::TENANT_CONNECTION;
            Artisan::call('migrate', [
                '--database' => $connectionName,
                '--path' => 'Modules/Core/database/migrations',
                '--force' => true,
            ]);

            // Disable foreign key checks for the seeding process (database-agnostic)
            Schema::disableForeignKeyConstraints();

            // Seed reference data (genders, marital statuses, nationalities, countries, currencies)
            Log::info('Seeding reference data for facility', ['facility_id' => $facility->id]);
            $referenceSeeder = new SeedReferenceDataAction;
            $referenceSeeder->execute($facility);

            // Seed branches
            Log::info('Seeding branches for facility', ['facility_id' => $facility->id]);
            $branchSeeder = new SeedBranchDataAction;
            $branchSeeder->execute();

            // Seed roles and permissions
            Log::info('Seeding roles and permissions for facility', ['facility_id' => $facility->id]);
            $rolesPermissionsSeeder = new SeedRolesAndPermissionsAction;
            $rolesPermissionsSeeder->execute();

            // Seed default facility settings
            Log::info('Seeding default settings for facility', ['facility_id' => $facility->id]);
            $settingsSeeder = new SeedSettingsDataAction;
            $settingsSeeder->execute($facility);

            // Seed departments
            Log::info('Seeding departments for facility', ['facility_id' => $facility->id]);
            $departmentSeeder = new SeedDepartmentDataAction;
            $departmentSeeder->execute();

            // Seed users
            Log::info('Seeding users for facility', ['facility_id' => $facility->id]);
            $userSeeder = new SeedUserDataAction;
            $userSeeder->execute();

            // Log final statistics for verification
            $this->logFinalStatistics($facility);

            Log::info('Successfully completed seeding for facility', ['facility_id' => $facility->id]);

        } finally {
            // Re-enable foreign key checks (database-agnostic)
            Schema::enableForeignKeyConstraints();

            // Switch back to the master database
            TenantDatabaseService::switchToMaster();
        }
    }

    /**
     * Log final seeding statistics for verification
     */
    private function logFinalStatistics(Facility $facility): void
    {
        try {
            // Reference data counts
            $genders = Gender::count();
            $maritalStatuses = MaritalStatus::count();
            $nationalities = Nationality::count();
            $countries = Country::count();
            $currencies = Currency::count();

            $users = User::count();
            $roles = Role::count();

            // Structural data counts
            $branches = Branch::count();
            $departments = Department::count();

            Log::info('Facility seeding completed');
            Log::info("Facility ID: {$facility->id}");
            Log::info("Facility Name: {$facility->name}");
            Log::info("Genders Created: {$genders}");
            Log::info("Marital Statuses Created: {$maritalStatuses}");
            Log::info("Nationalities Created: {$nationalities}");
            Log::info("Countries Created: {$countries}");
            Log::info("Currencies Created: {$currencies}");
            Log::info("Branches Created: {$branches}");
            Log::info("Roles Created: {$roles}");
            Log::info("Users Created: {$users}");
            Log::info("Departments Created: {$departments}");

        } catch (Exception $e) {
            Log::warning('Could not retrieve final statistics', [
                'facility_id' => $facility->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
