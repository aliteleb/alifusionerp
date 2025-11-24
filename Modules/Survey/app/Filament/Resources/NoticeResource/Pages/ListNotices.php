<?php

namespace Modules\Survey\Filament\Resources\NoticeResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Survey\Filament\Resources\NoticeResource;

class ListNotices extends ListRecords
{
    protected static string $resource = NoticeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
