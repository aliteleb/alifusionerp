<?php

namespace Modules\Survey\Filament\Resources\SurveyInvitationResource\Pages;

use App\Services\SurveyInvitationService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Modules\Survey\Filament\Resources\SurveyInvitationResource;

class ViewSurveyInvitation extends ViewRecord
{
    protected static string $resource = SurveyInvitationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),

            Actions\Action::make('copyUrl')
                ->label(__('Copy URL'))
                ->icon('heroicon-o-clipboard-document')
                ->color('gray')
                ->action(function () {
                    $invitationService = app(SurveyInvitationService::class);
                    $url = $invitationService->generateInvitationUrl($this->record);

                    Notification::make()
                        ->title(__('URL Ready'))
                        ->body($url)
                        ->success()
                        ->persistent()
                        ->send();
                }),

            Actions\Action::make('extend')
                ->label(__('Extend'))
                ->icon('heroicon-o-clock')
                ->color('warning')
                ->visible(fn () => $this->record->canBeAccessed())
                ->form([
                    \Filament\Forms\Components\TextInput::make('extend_hours')
                        ->label(__('Extend by (hours)'))
                        ->numeric()
                        ->default(24)
                        ->minValue(1)
                        ->maxValue(168)
                        ->required(),
                ])
                ->action(function (array $data) {
                    $invitationService = app(SurveyInvitationService::class);

                    try {
                        $invitationService->extendInvitation($this->record, $data['extend_hours']);

                        Notification::make()
                            ->title(__('Invitation Extended'))
                            ->body(__('Invitation extended by :hours hours', ['hours' => $data['extend_hours']]))
                            ->success()
                            ->send();

                        $this->refreshFormData(['expires_at']);
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title(__('Error'))
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Actions\Action::make('cancel')
                ->label(__('Cancel'))
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () => in_array($this->record->status, ['pending', 'sent', 'viewed']))
                ->requiresConfirmation()
                ->action(function () {
                    $invitationService = app(SurveyInvitationService::class);

                    try {
                        $invitationService->cancelInvitation($this->record);

                        Notification::make()
                            ->title(__('Invitation Cancelled'))
                            ->success()
                            ->send();

                        $this->refreshFormData(['status']);
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title(__('Error'))
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
