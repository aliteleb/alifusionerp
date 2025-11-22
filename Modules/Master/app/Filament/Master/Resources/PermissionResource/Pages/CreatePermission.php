<?php

namespace Modules\Master\Filament\Master\Resources\PermissionResource\Pages;

use Modules\Master\Filament\Master\Resources\PermissionResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePermission extends CreateRecord
{
    protected static string $resource = PermissionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
