<?php

namespace Modules\Survey\Filament\Resources\CustomerResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Survey\Filament\Resources\CustomerResource;

class CreateCustomer extends CreateRecord
{
    protected static string $resource = CustomerResource::class;
}
