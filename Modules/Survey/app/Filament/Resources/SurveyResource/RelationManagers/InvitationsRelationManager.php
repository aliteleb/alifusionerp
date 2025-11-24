<?php

namespace Modules\Survey\Filament\Resources\SurveyResource\RelationManagers;

use App\Models\SurveyInvitation;
use App\Services\SurveyInvitationService;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InvitationsRelationManager extends RelationManager
{
    protected static string $relationship = 'invitations';

    protected static ?string $recordTitleAttribute = 'invitation_token';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('invitation_token')
                    ->required()
                    ->maxLength(64),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('invitation_token')
            ->columns([
                Tables\Columns\TextColumn::make('customer.name')
                    ->label(__('Customer'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer_phone')
                    ->label(__('Phone'))
                    ->searchable()
                    ->copyable()
                    ->copyMessage(__('Phone copied'))
                    ->copyMessageDuration(1500),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'sent' => 'blue',
                        'viewed' => 'yellow',
                        'completed' => 'success',
                        'expired' => 'danger',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label(__('Expires At'))
                    ->dateTime()
                    ->sortable()
                    ->color(fn ($record) => $record->isExpired() ? 'danger' : 'gray'),

                Tables\Columns\TextColumn::make('view_count')
                    ->label(__('Views'))
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sent_at')
                    ->label(__('Sent At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('viewed_at')
                    ->label(__('Viewed At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('completed_at')
                    ->label(__('Completed At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => __('Pending'),
                        'sent' => __('Sent'),
                        'viewed' => __('Viewed'),
                        'completed' => __('Completed'),
                        'expired' => __('Expired'),
                        'cancelled' => __('Cancelled'),
                    ]),

                Tables\Filters\Filter::make('active')
                    ->label(__('Active Only'))
                    ->query(fn (Builder $query): Builder => $query->active()),

                Tables\Filters\Filter::make('expired')
                    ->label(__('Expired'))
                    ->query(fn (Builder $query): Builder => $query->expired()),
            ])
            ->headerActions([
                // Use the custom action we created
                \Modules\Survey\Filament\Actions\CreateSurveyInvitationsAction::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('copyUrl')
                    ->label(__('Copy URL'))
                    ->icon('heroicon-o-clipboard-document')
                    ->color('gray')
                    ->action(function (SurveyInvitation $record) {
                        $invitationService = app(SurveyInvitationService::class);
                        $url = $invitationService->generateInvitationUrl($record);

                        // In a real app, you'd use JavaScript to copy to clipboard
                        Notification::make()
                            ->title(__('URL Ready'))
                            ->body($url)
                            ->success()
                            ->persistent()
                            ->send();
                    }),

                Tables\Actions\Action::make('whatsapp')
                    ->label(__('WhatsApp'))
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->color('success')
                    ->url(function (SurveyInvitation $record) {
                        $invitationService = app(SurveyInvitationService::class);
                        $url = $invitationService->generateInvitationUrl($record);
                        $summary = $invitationService->getInvitationSummary($record);

                        // Get current locale for message
                        $locale = app()->getLocale();

                        // Build WhatsApp message based on locale
                        $messages = [
                            'ar' => "مرحباً {$summary['customer_name']}،\n\nيرجى المشاركة في استطلاع الرأي: {$summary['survey_title']}\n\n{$url}\n\nهذا الرابط صالح حتى: {$summary['expires_at']->format('Y-m-d H:i')}\n\nشكراً لك",
                            'ku' => "سڵاو {$summary['customer_name']}،\n\nتکایە لەم ڕاپرسییەدا بەشداری بکە: {$summary['survey_title']}\n\n{$url}\n\nئەم بەستەرە تا ئەم کاتەی بەردەستە: {$summary['expires_at']->format('Y-m-d H:i')}\n\nسوپاس",
                            'en' => "Hello {$summary['customer_name']},\n\nPlease participate in our survey: {$summary['survey_title']}\n\n{$url}\n\nThis link expires on: {$summary['expires_at']->format('Y-m-d H:i')}\n\nThank you",
                        ];

                        $message = $messages[$locale] ?? $messages['en'];
                        $phone = preg_replace('/[^0-9]/', '', $record->customer_phone);

                        return 'https://wa.me/'.$phone.'?text='.urlencode($message);
                    })
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('extend')
                    ->label(__('Extend'))
                    ->icon('heroicon-o-clock')
                    ->color('warning')
                    ->visible(fn (SurveyInvitation $record) => $record->canBeAccessed())
                    ->form([
                        Forms\Components\TextInput::make('extend_hours')
                            ->label(__('Extend by (hours)'))
                            ->numeric()
                            ->default(24)
                            ->minValue(1)
                            ->maxValue(168)
                            ->required(),
                    ])
                    ->action(function (SurveyInvitation $record, array $data) {
                        $invitationService = app(SurveyInvitationService::class);

                        try {
                            $invitationService->extendInvitation($record, $data['extend_hours']);

                            Notification::make()
                                ->title(__('Invitation Extended'))
                                ->body(__('Invitation extended by :hours hours', ['hours' => $data['extend_hours']]))
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title(__('Error'))
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Tables\Actions\Action::make('cancel')
                    ->label(__('Cancel'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (SurveyInvitation $record) => in_array($record->status, ['pending', 'sent', 'viewed']))
                    ->requiresConfirmation()
                    ->action(function (SurveyInvitation $record) {
                        $invitationService = app(SurveyInvitationService::class);

                        try {
                            $invitationService->cancelInvitation($record);

                            Notification::make()
                                ->title(__('Invitation Cancelled'))
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title(__('Error'))
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('cancel')
                    ->label(__('Cancel Selected'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                        $invitationService = app(SurveyInvitationService::class);
                        $count = 0;

                        foreach ($records as $record) {
                            if (in_array($record->status, ['pending', 'sent', 'viewed'])) {
                                $invitationService->cancelInvitation($record);
                                $count++;
                            }
                        }

                        Notification::make()
                            ->title(__('Invitations Cancelled'))
                            ->body(__('Cancelled :count invitations', ['count' => $count]))
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
