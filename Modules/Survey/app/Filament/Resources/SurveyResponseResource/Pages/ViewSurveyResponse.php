<?php

namespace Modules\Survey\Filament\Resources\SurveyResponseResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Modules\Survey\Filament\Resources\SurveyResponseResource;

class ViewSurveyResponse extends ViewRecord
{
    protected static string $resource = SurveyResponseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
