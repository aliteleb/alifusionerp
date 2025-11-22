<?php

namespace Modules\Master\Filament\Master\Resources\PermissionResource\Pages;

use Modules\Master\Filament\Master\Resources\PermissionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPermissions extends ListRecords
{
    protected static string $resource = PermissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label(__('Create Permission')),
        ];
    }
}
