<?php

namespace Modules\Survey\Filament\Resources\SurveyInvitationResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Survey\Filament\Resources\SurveyInvitationResource;

class CreateSurveyInvitation extends CreateRecord
{
    protected static string $resource = SurveyInvitationResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
