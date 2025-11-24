<?php

namespace Modules\Survey\Filament\Resources\CustomerResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Survey\Filament\Resources\CustomerResource;

class EditCustomer extends EditRecord
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
