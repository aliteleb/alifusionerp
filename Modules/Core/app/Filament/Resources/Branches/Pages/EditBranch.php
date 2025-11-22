<?php

namespace Modules\Core\Filament\Resources\Branches\Pages;

use Modules\Core\Filament\Resources\Branches\BranchResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditBranch extends EditRecord
{
    protected static string $resource = BranchResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title(__('Branch updated successfully'))
            ->body(__('The branch information has been updated.'));
    }

    protected function getHeaderActions(): array
    {
        return [
            // Removed WhatsApp test action and other actions for simplicity
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Clean phone numbers before saving
        if (isset($data['contact_phone'])) {
            $data['contact_phone'] = $this->cleanPhoneNumber($data['contact_phone']);
        }

        return $data;
    }

    private function cleanPhoneNumber(?string $phone): ?string
    {
        if (! $phone) {
            return null;
        }

        // Remove all non-digit characters except +
        $cleaned = preg_replace('/[^+\d]/', '', $phone);

        // Ensure + prefix for international numbers
        if ($cleaned && ! str_starts_with($cleaned, '+') && strlen($cleaned) > 10) {
            $cleaned = '+'.$cleaned;
        }

        return $cleaned;
    }
}


