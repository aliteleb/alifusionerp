<?php

namespace Modules\Core\Filament\Resources\Branches\Pages;

use Modules\Core\Filament\Resources\Branches\BranchResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateBranch extends CreateRecord
{
    protected static string $resource = BranchResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title(__('Branch created successfully'))
            ->body(__('The branch has been created and is now available.'));
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Ensure is_active defaults to true if not set
        $data['is_active'] = $data['is_active'] ?? true;

        // Clean phone numbers
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


