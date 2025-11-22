<?php

namespace Modules\Core\Filament\Resources\Users\Pages;

use Modules\Core\Filament\Resources\Users\UserResource;
use Modules\Core\Services\DatabaseNotificationService;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        DatabaseNotificationService::sendCreatedNotification(
            record: $this->record,
            modelType: 'User',
            titleField: 'name',
            icon: 'heroicon-o-user',
            status: 'success',
            resourceClass: UserResource::class
        );
    }
}


