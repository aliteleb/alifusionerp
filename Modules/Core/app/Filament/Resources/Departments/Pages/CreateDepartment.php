<?php

namespace Modules\Core\Filament\Resources\Departments\Pages;

use Modules\Core\Filament\Resources\Departments\DepartmentResource;
use Modules\Core\Services\DatabaseNotificationService;
use Filament\Resources\Pages\CreateRecord;

class CreateDepartment extends CreateRecord
{
    protected static string $resource = DepartmentResource::class;

    protected function afterCreate(): void
    {
        DatabaseNotificationService::sendCreatedNotification(
            record: $this->record,
            modelType: 'Department',
            titleField: 'name',
            icon: 'heroicon-o-building-office',
            status: 'success'
        );
    }
}


