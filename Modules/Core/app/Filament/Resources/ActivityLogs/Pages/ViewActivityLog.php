<?php

namespace Modules\Core\Filament\Resources\ActivityLogs\Pages;

use Modules\Core\Filament\Resources\ActivityLogs\ActivityLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewActivityLog extends ViewRecord
{
    protected static string $resource = ActivityLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No edit or delete actions needed
        ];
    }
}


