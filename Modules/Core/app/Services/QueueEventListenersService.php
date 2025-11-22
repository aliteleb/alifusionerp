<?php

namespace Modules\Core\Services;

use Modules\System\Actions\Tenant\BootTenantAction;
use Modules\Master\Entities\Facility;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

class QueueEventListenersService
{
    /**
     * Register queue event listeners for monitoring
     */
    public static function register(): void
    {
        // Before job starts processing
        Queue::before(function (JobProcessing $event) {
            if (! ($event->job instanceof \Illuminate\Queue\Jobs\DatabaseJob) || ! $event->job) {
                return;
            }

            /** @var \Illuminate\Queue\Jobs\DatabaseJob $job */
            $job = $event->job;
            $record = $job->getJobRecord();

            $jobName = $job->resolveName();
            $payload = $job->getRawBody();
            $facilityId = $record->facility_id;

            // Switch to default connection
            TenantDatabaseService::switchToMaster();

            // get facility
            $facility = Facility::find($facilityId);

            // Configure tenant filesystem
            if ($facility) {
                // Connect to facility
                TenantDatabaseService::connectToFacility($facility);

                // The action will handle facility detection and configuration internally
                BootTenantAction::configureTenant($facility);
            }

            Log::info('Queue Job Started', [
                'job' => $jobName,
                'facility_id' => $facilityId,
                'queue' => $job->getQueue(),
                'attempts' => $job->attempts(),
            ]);
        });

        // After job successfully processed
        Queue::after(function (JobProcessed $event) {
            if (! ($event->job instanceof \Illuminate\Queue\Jobs\DatabaseJob) || ! $event->job) {
                return;
            }

            /** @var \Illuminate\Queue\Jobs\DatabaseJob $job */
            $job = $event->job;
            $record = $job->getJobRecord();

            $jobName = $job->resolveName();
            $payload = $job->getRawBody();
            $facilityId = $record->facility_id;

            Log::info('Queue Job Finished', [
                'job' => $jobName,
                'facility_id' => $facilityId,
                'queue' => $job->getQueue(),
            ]);
        });

        // When job fails
        Queue::failing(function (JobFailed $event) {
            if (! ($event->job instanceof \Illuminate\Queue\Jobs\DatabaseJob) || ! $event->job) {
                return;
            }

            /** @var \Illuminate\Queue\Jobs\DatabaseJob $job */
            $job = $event->job;
            $record = $job->getJobRecord();

            $jobName = $job->resolveName();
            $payload = $job->getRawBody();
            $facilityId = $record->facility_id;

            Log::error('Queue Job Failed', [
                'job' => $jobName,
                'facility_id' => $facilityId,
                'queue' => $job->getQueue(),
                'exception' => $event->exception->getMessage(),
                'attempts' => $job->attempts(),
            ]);
        });
    }
}
