<?php

namespace Modules\Core\Observers;

use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Context;

class DatabaseNotificationObserver
{
    protected function normalizeIcon(array $data): array
    {
        if (! isset($data['icon']) || ! is_string($data['icon'])) {
            return $data;
        }

        $icon = $data['icon'];

        if ($icon === '' || str_starts_with($icon, 'heroicon-')) {
            return $data;
        }

        $data['icon'] = 'heroicon-o-'.$icon;

        return $data;
    }

    /**
     * Handle the DatabaseNotification "creating" event.
     */
    public function creating(DatabaseNotification $databaseNotification): void
    {
        $branch_id = Context::get('branch_id_for_notification', null);
        $databaseNotification->branch_id = $branch_id;
        $databaseNotification->data = $this->normalizeIcon($databaseNotification->data ?? []);
    }

    /**
     * Handle the DatabaseNotification "created" event.
     */
    public function created(DatabaseNotification $databaseNotification): void
    {
        //
    }

    /**
     * Handle the DatabaseNotification "updated" event.
     */
    public function updated(DatabaseNotification $databaseNotification): void
    {
        //
    }

    /**
     * Handle the DatabaseNotification "deleted" event.
     */
    public function deleted(DatabaseNotification $databaseNotification): void
    {
        //
    }

    /**
     * Handle the DatabaseNotification "restored" event.
     */
    public function restored(DatabaseNotification $databaseNotification): void
    {
        //
    }

    /**
     * Handle the DatabaseNotification "force deleted" event.
     */
    public function forceDeleted(DatabaseNotification $databaseNotification): void
    {
        //
    }

    public function retrieved(DatabaseNotification $databaseNotification): void
    {
        $databaseNotification->data = $this->normalizeIcon($databaseNotification->data ?? []);
    }
}
