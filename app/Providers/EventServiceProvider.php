<?php

namespace App\Providers;

use App\Listeners\SetJobFacilityId;
use Modules\Core\Services\QueueEventListenersService;
use Modules\Core\Services\TenantDatabaseService;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Queue\Events\JobQueued;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        JobQueued::class => [
            SetJobFacilityId::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        // Register model observers

        // Register queue event listeners
        QueueEventListenersService::register();
        
        $this->customizeQueuePayload();
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }

    /**
     * Add tenant metadata to queued job payloads.
     */
    protected function customizeQueuePayload(): void
    {
        Queue::createPayloadUsing(function ($connection, $queue, $payload) {
            $facilityId = null;

            $serializedCommand = $payload['data']['command'] ?? null;

            if (is_string($serializedCommand)) {
                try {
                    $command = unserialize($serializedCommand);

                    if (is_object($command) && property_exists($command, 'tenantFacilityId')) {
                        $facilityId = $command->tenantFacilityId ?: null;
                    }
                } catch (\Throwable $exception) {
                    Log::debug('Failed to extract tenant facility from queued job payload', [
                        'queue' => $queue,
                        'error' => $exception->getMessage(),
                    ]);
                }
            }

            if (! $facilityId) {
                $facility = TenantDatabaseService::getCurrentFacility();

                if ($facility) {
                    $facilityId = $facility->id;
                }
            }

            return [
                'facility_id' => $facilityId ? (int) $facilityId : null,
            ];
        });
    }
}
