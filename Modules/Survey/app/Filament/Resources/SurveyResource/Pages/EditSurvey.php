<?php

namespace Modules\Survey\Filament\Resources\SurveyResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Survey\Filament\Resources\SurveyResource;

class EditSurvey extends EditRecord
{
    protected static string $resource = SurveyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
