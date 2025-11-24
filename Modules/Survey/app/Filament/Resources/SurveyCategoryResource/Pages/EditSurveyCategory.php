<?php

namespace Modules\Survey\Filament\Resources\SurveyCategoryResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Survey\Filament\Resources\SurveyCategoryResource;

class EditSurveyCategory extends EditRecord
{
    protected static string $resource = SurveyCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
