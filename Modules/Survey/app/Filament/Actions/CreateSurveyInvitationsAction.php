<?php

namespace Modules\Survey\Filament\Actions;

use App\Models\Customer;
use App\Models\Survey;
use App\Models\SurveyInvitation;
use App\Services\SurveyInvitationService;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Support\Enums\MaxWidth;

class CreateSurveyInvitationsAction
{
    public static function make(): Action
    {
        return Action::make('createInvitations')
            ->label(__('Create Invitations'))
            ->icon('heroicon-o-paper-airplane')
            ->color('success')
            ->modalWidth(MaxWidth::FourExtraLarge)
            ->form([
                Forms\Components\Section::make(__('Customer Selection'))
                    ->schema([
                        Forms\Components\CheckboxList::make('customer_ids')
                            ->label(__('Select Customers'))
                            ->required()
                            ->options(function () {
                                return Customer::orderBy('name')
                                    ->pluck('name', 'id')
                                    ->toArray();
                            })
                            ->columns(3)
                            ->searchable()
                            ->bulkToggleable()
                            ->gridDirection('row'),
                    ]),

                Forms\Components\Section::make(__('Invitation Settings'))
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('invitation_type')
                                    ->label(__('Invitation Type'))
                                    ->options([
                                        'direct' => __('Direct'),
                                        'campaign' => __('Campaign'),
                                        'reminder' => __('Reminder'),
                                    ])
                                    ->default('campaign')
                                    ->required(),

                                Forms\Components\TextInput::make('expires_in_hours')
                                    ->label(__('Expires In (Hours)'))
                                    ->numeric()
                                    ->default(24)
                                    ->minValue(1)
                                    ->maxValue(168) // 7 days
                                    ->required()
                                    ->suffix(__('hours')),
                            ]),

                        Forms\Components\Toggle::make('send_immediately')
                            ->label(__('Mark as Sent Immediately'))
                            ->helperText(__('Mark invitations as sent after creation'))
                            ->default(true),

                        Forms\Components\Toggle::make('exclude_existing')
                            ->label(__('Exclude Existing Active Invitations'))
                            ->helperText(__('Skip customers who already have active invitations'))
                            ->default(true),
                    ]),
            ])
            ->action(function (array $data, Survey $record): void {
                $invitationService = app(SurveyInvitationService::class);

                try {
                    // Get selected customers
                    $customers = Customer::whereIn('id', $data['customer_ids'])->get();

                    // Filter out customers with existing active invitations if requested
                    if ($data['exclude_existing']) {
                        $existingCustomerIds = SurveyInvitation::where('survey_id', $record->id)
                            ->active()
                            ->pluck('customer_id')
                            ->toArray();

                        $customers = $customers->whereNotIn('id', $existingCustomerIds);
                    }

                    if ($customers->isEmpty()) {
                        Notification::make()
                            ->title(__('No customers to invite'))
                            ->body(__('All selected customers already have active invitations for this survey.'))
                            ->warning()
                            ->send();

                        return;
                    }

                    // Create bulk invitations
                    $invitations = $invitationService->createBulkInvitations(
                        $record,
                        $customers,
                        $data['expires_in_hours'],
                        $data['invitation_type']
                    );

                    // For large batches, use queue with proper facility context
                    if ($customers->count() > 50 && $data['send_immediately']) {
                        // Get current facility for queue context
                        $facility = getCurrentFacility();

                        \App\Jobs\ProcessSurveyInvitationsJob::dispatch(
                            $record,
                            $customers->pluck('id'),
                            [
                                'expires_in_hours' => $data['expires_in_hours'],
                                'invitation_type' => $data['invitation_type'],
                                'send_immediately' => true,
                            ],
                            $facility
                        );

                        Notification::make()
                            ->title(__('Large Batch Queued'))
                            ->body(__('Large batch of :count invitations queued for background processing', [
                                'count' => $customers->count(),
                            ]))
                            ->success()
                            ->send();

                        return;
                    }

                    // Mark as sent if requested (small batches)
                    if ($data['send_immediately']) {
                        foreach ($invitations as $invitation) {
                            $invitation->update(['status' => 'sent', 'sent_at' => now(), 'sent_via' => 'filament']);
                        }
                    }

                    Notification::make()
                        ->title(__('Invitations Created Successfully'))
                        ->body(__('Created :count invitations for survey: :survey', [
                            'count' => $invitations->count(),
                            'survey' => $record->getTranslation('title', 'en'),
                        ]))
                        ->success()
                        ->send();

                } catch (\Exception $e) {
                    Notification::make()
                        ->title(__('Error Creating Invitations'))
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}
