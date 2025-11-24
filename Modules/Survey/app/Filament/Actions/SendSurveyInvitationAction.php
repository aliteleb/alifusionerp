<?php

namespace Modules\Survey\Filament\Actions;

use App\Models\Customer;
use App\Models\Survey;
use App\Services\SurveyInvitationService;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class SendSurveyInvitationAction
{
    public static function make(): Action
    {
        return Action::make('sendToCustomer')
            ->label(__('Send to Customer'))
            ->icon('heroicon-o-paper-airplane')
            ->color('success')
            ->visible(fn ($record) => $record->status === 'active')
            ->form([
                Forms\Components\Select::make('customer_id')
                    ->label(__('Select Customer'))
                    ->placeholder(__('Search and select a customer'))
                    ->searchable()
                    ->preload()
                    ->live()
                    ->options(function () {
                        return Customer::query()
                            ->select('id', 'name', 'phone', 'email')
                            ->orderBy('name')
                            ->get()
                            ->mapWithKeys(function ($customer) {
                                return [
                                    $customer->id => sprintf(
                                        '%s (%s) - %s',
                                        $customer->name,
                                        $customer->phone,
                                        $customer->email ?: __('No email')
                                    ),
                                ];
                            });
                    })
                    ->required(),

                Forms\Components\Radio::make('invitation_type')
                    ->label(__('Invitation Type'))
                    ->options([
                        'single_use' => __('Single Use (expires after completion)'),
                        'time_limited' => __('Time Limited (expires after specified time)'),
                        'permanent' => __('Permanent (no expiration)'),
                    ])
                    ->default('time_limited')
                    ->live()
                    ->required(),

                Forms\Components\DateTimePicker::make('expires_at')
                    ->label(__('Expiration Date & Time'))
                    ->visible(fn (Forms\Get $get): bool => $get('invitation_type') === 'time_limited')
                    ->default(now()->addDays(7))
                    ->minDate(now())
                    ->required(fn (Forms\Get $get): bool => $get('invitation_type') === 'time_limited'),

                Forms\Components\Toggle::make('send_whatsapp')
                    ->label(__('Send WhatsApp Message'))
                    ->default(true)
                    ->live(),

                Forms\Components\Textarea::make('custom_message')
                    ->label(__('Custom Message (Optional)'))
                    ->visible(fn (Forms\Get $get): bool => (bool) $get('send_whatsapp'))
                    ->placeholder(__('Leave empty to use default message template'))
                    ->rows(3),

                Forms\Components\ViewField::make('message_preview')
                    ->label(__('Message Preview'))
                    ->visible(fn (Forms\Get $get): bool => (bool) $get('send_whatsapp') && ! empty($get('customer_id')))
                    ->view('filament.forms.components.message-preview')
                    ->viewData(function (Forms\Get $get) {
                        if (! $get('customer_id')) {
                            return ['preview' => __('Select a customer to see message preview')];
                        }

                        $customer = Customer::find($get('customer_id'));
                        if (! $customer) {
                            return ['preview' => __('Customer not found')];
                        }

                        $customMessage = $get('custom_message');
                        if ($customMessage) {
                            return ['preview' => $customMessage];
                        }

                        // Generate default message preview using service
                        $invitationService = app(SurveyInvitationService::class);
                        $previewUrl = '{{ INVITATION_URL }}';
                        $message = $invitationService->getDefaultMessage($customer->name, 'Survey Title', $previewUrl);

                        return ['preview' => $message];
                    }),
            ])
            ->action(function (array $data, Survey $record): void {
                $invitationService = app(SurveyInvitationService::class);

                try {
                    $customer = Customer::findOrFail($data['customer_id']);

                    // Check if customer already has active invitation
                    if ($invitationService->hasActiveInvitation($record, $customer)) {
                        Notification::make()
                            ->title(__('Customer Already Invited'))
                            ->body(__('This customer already has an active invitation for this survey.'))
                            ->warning()
                            ->send();

                        return;
                    }

                    // Create survey invitation
                    $invitation = $invitationService->createInvitation($record, $customer, $data);

                    // Send WhatsApp message if enabled
                    if ($data['send_whatsapp']) {
                        $invitationService->sendWhatsAppMessage(
                            $invitation,
                            $record,
                            $customer,
                            $data['custom_message'] ?? null
                        );
                    }

                    Notification::make()
                        ->title(__('Survey invitation sent successfully'))
                        ->body(sprintf(
                            __('Invitation sent to %s (%s)'),
                            $customer->name,
                            $customer->phone
                        ))
                        ->success()
                        ->send();

                    Log::info('Survey invitation sent successfully', [
                        'survey_id' => $record->id,
                        'customer_id' => $customer->id,
                        'invitation_id' => $invitation->id,
                        'whatsapp_sent' => $data['send_whatsapp'],
                    ]);

                } catch (\Exception $e) {
                    Log::error('Failed to send survey invitation', [
                        'survey_id' => $record->id,
                        'customer_id' => $data['customer_id'] ?? null,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);

                    Notification::make()
                        ->title(__('Failed to send survey invitation'))
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}
