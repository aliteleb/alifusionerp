<?php

namespace Modules\Core\Traits;

use Modules\Master\Entities\Facility;
use Modules\Core\Services\TenantDatabaseService;

trait TenantAware
{
    /**
     * The tenant facility identifier attached to the job.
     */
    public ?int $tenantFacilityId = null;

    /**
     * Attach tenant context to the job.
     */
    public function setTenant(?Facility $facility): self
    {
        if ($facility) {
            $this->tenantFacilityId = $facility->id;
        }

        return $this;
    }

    /**
     * Switch the active database connection to the tenant context.
     */
    protected function switchToTenantContext(): void
    {
        if (! $this->tenantFacilityId) {
            return;
        }

        TenantDatabaseService::switchToMaster();

        $facility = Facility::find($this->tenantFacilityId);

        if ($facility) {
            TenantDatabaseService::setCurrentFacility($facility);
            TenantDatabaseService::switchToTenant($facility);
        }
    }

    /**
     * Resolve the facility associated with the job.
     */
    protected function getTenantFacility(): ?Facility
    {
        if (! $this->tenantFacilityId) {
            return null;
        }

        $currentConnection = TenantDatabaseService::getCurrentTenantConnection();

        if ($currentConnection) {
            TenantDatabaseService::switchToMaster();
        }

        $facility = Facility::find($this->tenantFacilityId);

        if ($currentConnection && $facility) {
            TenantDatabaseService::switchToTenant($facility);
        }

        return $facility;
    }

    /**
     * Restore the master connection after the job has finished processing.
     */
    protected function restoreDefaultConnection(): void
    {
        TenantDatabaseService::switchToMaster();
    }
}
