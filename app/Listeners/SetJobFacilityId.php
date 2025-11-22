<?php

namespace App\Listeners;

use Modules\Core\Entities\Job;
use Modules\Core\Services\TenantDatabaseService;
use Illuminate\Queue\Events\JobQueued;
use Illuminate\Support\Facades\Log;

class SetJobFacilityId
{
    /**
     * Handle the event.
     */
    public function handle(JobQueued $event): void
    {
        if ($event->connectionName !== 'database') {
            return;
        }

        try {
            $facility = TenantDatabaseService::getCurrentFacility();

            if (! $facility) {
                Log::debug('No facility context detected for queued job', [
                    'job_id' => $event->id,
                    'connection' => $event->connectionName,
                ]);

                return;
            }

            $job = Job::find($event->id);

            if (! $job) {
                Log::debug('Queued job record not found when setting facility context', [
                    'job_id' => $event->id,
                ]);

                return;
            }

            $job->update(['facility_id' => $facility->id]);
        } catch (\Throwable $exception) {
            Log::warning('Failed to set facility context for queued job', [
                'job_id' => $event->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
