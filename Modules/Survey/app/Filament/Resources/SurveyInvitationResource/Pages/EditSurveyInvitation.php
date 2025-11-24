<?php

namespace Modules\Survey\Filament\Resources\SurveyInvitationResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Survey\Filament\Resources\SurveyInvitationResource;

class EditSurveyInvitation extends EditRecord
{
    protected static string $resource = SurveyInvitationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
