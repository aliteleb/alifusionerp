<?php

namespace Modules\Core\Observers;

use Modules\System\Actions\Facility\SeedFacilityDataAction;
use Modules\Master\Entities\Facility;
use Modules\Core\Services\TenantDatabaseService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FacilityObserver
{
    /**
     * Handle the Facility "creating" event.
     */
    public function creating(Facility $facility): void
    {
        // Set the correct database name using TenantDatabaseService
        if (empty($facility->database_name)) {
            $facility->database_name = TenantDatabaseService::getTenantDatabaseName($facility);
        }
    }

    /**
     * Handle the Facility "created" event.
     */
    public function created(Facility $facility): void
    {
        // Check if we're currently seeding to avoid duplicate seeding
        if ($this->isSeeding()) {
            Log::info('Skipping tenant database creation during seeding', [
                'facility_id' => $facility->id,
                'facility_name' => $facility->name,
            ]);

            return;
        }

        // Defer tenant database creation until after the current transaction is committed
        DB::afterCommit(function () use ($facility) {
            $this->createTenantDatabase($facility);
        });
    }

    /**
     * Check if we're currently seeding
     */
    private function isSeeding(): bool
    {
        // Check if we're running in the console and if it's a seeding command
        if (app()->runningInConsole()) {
            $argv = $_SERVER['argv'] ?? [];
            foreach ($argv as $arg) {
                if (str_contains($arg, 'seed')) {
                    return true;
                }
            }
        }

        // Check if the DatabaseSeeder is running
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        foreach ($trace as $call) {
            if (isset($call['class']) && str_contains($call['class'], 'DatabaseSeeder')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Create tenant database for the facility
     */
    private function createTenantDatabase(Facility $facility): void
    {
        try {
            Log::info('Creating tenant database for new facility', [
                'facility_id' => $facility->id,
                'facility_name' => $facility->name,
            ]);

            // Create and seed tenant database
            $seedAction = new SeedFacilityDataAction;
            $seedAction->execute($facility);

            Log::info('Successfully created tenant database for facility', [
                'facility_id' => $facility->id,
            ]);

        } catch (Exception $e) {
            Log::error('Failed to create tenant database for facility', [
                'facility_id' => $facility->id,
                'error' => $e->getMessage(),
            ]);

            // You may want to handle this differently based on your requirements
            // For now, we'll just log the error
        }
    }

    /**
     * Handle the Facility "updating" event.
     */
    public function updating(Facility $facility): void
    {
        // If subdomain is being changed, update the database name accordingly
        if ($facility->isDirty('subdomain') || empty($facility->database_name)) {
            $facility->database_name = TenantDatabaseService::getTenantDatabaseName($facility);
        }
    }

    /**
     * Handle the Facility "updated" event.
     */
    public function updated(Facility $facility): void
    {
        // Handle any necessary updates to tenant database if needed
        // For example, if subdomain changes, you might need to rename the database
        if ($facility->wasChanged('subdomain')) {
            Log::warning('Facility subdomain changed - manual database rename may be required', [
                'facility_id' => $facility->id,
                'old_subdomain' => $facility->getOriginal('subdomain'),
                'new_subdomain' => $facility->subdomain,
                'old_database_name' => $facility->getOriginal('database_name'),
                'new_database_name' => $facility->database_name,
            ]);
        }
    }

    /**
     * Handle the Facility "deleted" event.
     */
    public function deleted(Facility $facility): void
    {
        // Optionally handle tenant database cleanup
        // Note: This is commented out for safety - you may want to implement a soft delete approach
        //
        // try {
        //     TenantDatabaseService::dropTenantDatabase($facility);
        //     Log::info('Dropped tenant database for deleted facility', [
        //         'facility_id' => $facility->id
        //     ]);
        // } catch (\Exception $e) {
        //     Log::error('Failed to drop tenant database for deleted facility', [
        //         'facility_id' => $facility->id,
        //         'error' => $e->getMessage()
        //     ]);
        // }
    }

    /**
     * Handle the Facility "restored" event.
     */
    public function restored(Facility $facility): void
    {
        // If you implement soft deletes and restore functionality,
        // you might want to recreate the tenant database here
    }

    /**
     * Handle the Facility "force deleted" event.
     */
    public function forceDeleted(Facility $facility): void
    {
        // Defer tenant database deletion until after the current transaction is committed
        DB::afterCommit(function () use ($facility) {
            $this->deleteTenantDatabase($facility);
        });
    }

    /**
     * Delete tenant database for the facility
     */
    private function deleteTenantDatabase(Facility $facility): void
    {
        try {
            Log::info('Force deleting facility - dropping tenant database', [
                'facility_id' => $facility->id,
                'facility_name' => $facility->name,
            ]);

            // Drop the tenant database
            TenantDatabaseService::dropTenantDatabase($facility);

            Log::info('Successfully dropped tenant database for force deleted facility', [
                'facility_id' => $facility->id,
            ]);

        } catch (Exception $e) {
            Log::error('Failed to drop tenant database for force deleted facility', [
                'facility_id' => $facility->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
