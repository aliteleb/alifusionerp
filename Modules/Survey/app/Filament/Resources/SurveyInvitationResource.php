<?php

namespace Modules\Survey\Filament\Resources;

use App\Enums\InvitationStatusEnum;
use App\Enums\SentViaEnum;
use App\Models\SurveyInvitation;
use App\Models\User;
use App\Services\SurveyInvitationService;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Modules\Survey\Filament\Resources\SurveyInvitationResource\Pages;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;
use Ysfkaya\FilamentPhoneInput\PhoneInputNumberType;
use Ysfkaya\FilamentPhoneInput\Tables\PhoneColumn;

class SurveyInvitationResource extends Resource
{
    protected static ?string $model = SurveyInvitation::class;

    protected static BackedEnum|string|null $navigationIcon = Heroicon::PaperAirplane;

    protected static ?int $navigationSort = 6;

    public static function getNavigationLabel(): string
    {
        return __('Survey Invitations');
    }

    public static function getModelLabel(): string
    {
        return __('Survey Invitation');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Survey Invitations');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Survey Management');
    }

    public static function canAccess(): bool
    {
        /** @var User $user */
        $user = Filament::getCurrentPanel()->auth()->user();

        return $user ? $user->can('access_invitations') : false;
    }

    public static function canViewAny(): bool
    {
        /** @var User $user */
        $user = Filament::getCurrentPanel()->auth()->user();

        return $user ? $user->can('view_invitations') : false;
    }

    public static function canView($record): bool
    {
        /** @var User $user */
        $user = Filament::getCurrentPanel()->auth()->user();

        return $user ? $user->can('view_invitations') : false;
    }

    public static function canCreate(): bool
    {
        /** @var User $user */
        $user = Filament::getCurrentPanel()->auth()->user();

        return $user ? $user->can('create_invitations') : false;
    }

    public static function canEdit($record): bool
    {
        /** @var User $user */
        $user = Filament::getCurrentPanel()->auth()->user();

        return $user ? $user->can('edit_invitations') : false;
    }

    public static function canDelete($record): bool
    {
        /** @var User $user */
        $user = Filament::getCurrentPanel()->auth()->user();

        return $user ? $user->can('delete_invitations') : false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Section::make(__('Invitation Details'))
                    ->compact()
                    ->schema([
                        Forms\Components\Select::make('survey_id')
                            ->label(__('Survey'))
                            ->options(function () {
                                return \App\Models\Survey::orderedByTitle()
                                    ->get()
                                    ->mapWithKeys(function ($survey) {
                                        return [$survey->id => $survey->getTranslation('title', app()->getLocale())];
                                    });
                            })
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('customer_id')
                            ->label(__('Customer'))
                            ->relationship(
                                name: 'customer',
                                titleAttribute: 'name',
                            )
                            ->searchableBy(
                                ['name', 'phone', 'email'],
                                \App\Models\Customer::class,
                                labelFormatter: fn ($record) => $record->name.($record->phone ? ' ('.$record->phone.')' : '')
                            )
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\TextInput::make('name')
                                        ->label(__('Customer Name'))
                                        ->required()
                                        ->maxLength(255)
                                        ->columnSpan(2),

                                    PhoneInput::make('phone')
                                        ->label(__('Phone'))
                                        ->required()
                                        ->placeholder(__('Enter phone number'))
                                        ->helperText(__('Enter phone number with country code'))
                                        ->prefixIcon('heroicon-o-phone')
                                        ->unique(\App\Models\Customer::class, 'phone', ignoreRecord: true)
                                        ->columnSpan(1)
                                        ->defaultCountry('IQ') // Default to Iraq
                                        ->displayNumberFormat(PhoneInputNumberType::NATIONAL)
                                        ->inputNumberFormat(PhoneInputNumberType::INTERNATIONAL),

                                    Forms\Components\TextInput::make('email')
                                        ->label(__('Email Address'))
                                        ->email()
                                        ->unique('customers', 'email')
                                        ->maxLength(255),
                                ]),

                                Forms\Components\Grid::make(3)->schema([
                                    Forms\Components\Select::make('gender_id')
                                        ->label(__('Gender'))
                                        ->relationship(
                                            name: 'gender',
                                            titleAttribute: 'name',
                                            modifyQueryUsing: fn (\Illuminate\Database\Eloquent\Builder $query) => $query->orderByRaw("name->>'".app()->getLocale()."' ASC")
                                        )
                                        ->searchable()
                                        ->preload(),

                                    Forms\Components\DatePicker::make('birthday')
                                        ->label(__('Birth Date'))
                                        ->maxDate(now()),

                                    Forms\Components\Select::make('branch_id')
                                        ->label(__('Branch'))
                                        ->relationship(
                                            name: 'branch',
                                            titleAttribute: 'name',
                                            modifyQueryUsing: fn (\Illuminate\Database\Eloquent\Builder $query) => $query->orderByRaw("name->>'".app()->getLocale()."' ASC")
                                        )
                                        ->required()
                                        ->searchable()
                                        ->preload(),
                                ]),

                                Forms\Components\Textarea::make('address')
                                    ->label(__('Address'))
                                    ->rows(2)
                                    ->columnSpanFull(),

                                Forms\Components\Textarea::make('notes')
                                    ->label(__('Notes'))
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ])
                            ->createOptionUsing(function (array $data): int {
                                // Add current user as creator and default visit time
                                $data['created_by'] = Auth::id();
                                $data['updated_by'] = Auth::id();
                                $data['visit_time'] = $data['visit_time'] ?? now();

                                $customer = \App\Models\Customer::create($data);

                                return $customer->id;
                            })
                            ->createOptionModalHeading(__('Create New Customer'))
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->name.($record->phone ? ' ('.$record->phone.')' : '')),

                        Forms\Components\TextInput::make('invitation_token')
                            ->label(__('Invitation Token'))
                            ->visibleOn('edit')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\Select::make('status')
                            ->label(__('Status'))
                            ->options(\App\Enums\InvitationStatusEnum::class)
                            ->default('pending')
                            ->visibleOn('edit')
                            ->disabledOn('create')
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make(__('Timing & Expiration'))
                    ->compact()
                    ->schema([
                        Forms\Components\DateTimePicker::make('send_after')
                            ->label(__('Send After'))
                            ->helperText(__('The earliest time this invitation can be sent'))
                            ->default(now())
                            ->nullable(),

                        Forms\Components\DateTimePicker::make('expires_at')
                            ->label(__('Expiration Date & Time'))
                            ->default(now()->addDay())
                            ->minDate(now()),

                        Forms\Components\DateTimePicker::make('sent_at')
                            ->label(__('Sent At'))
                            ->disabled()
                            ->visibleOn('edit')
                            ->dehydrated(false),

                        Forms\Components\DateTimePicker::make('viewed_at')
                            ->label(__('Viewed At'))
                            ->disabled()
                            ->visibleOn('edit')
                            ->dehydrated(false),

                        Forms\Components\DateTimePicker::make('completed_at')
                            ->label(__('Completed At'))
                            ->disabled()
                            ->visibleOn('edit')
                            ->dehydrated(false),
                    ])
                    ->columns(2),

                Forms\Components\Section::make(__('Contact Information'))
                    ->visibleOn('edit')
                    ->schema([
                        PhoneInput::make('customer_phone')
                            ->label(__('Customer Phone'))
                            ->displayNumberFormat(PhoneInputNumberType::NATIONAL)
                            ->inputNumberFormat(PhoneInputNumberType::INTERNATIONAL),

                        Forms\Components\TextInput::make('customer_email')
                            ->label(__('Customer Email'))
                            ->email()
                            ->maxLength(255),

                        Forms\Components\Select::make('sent_via')
                            ->label(__('Sent Via'))
                            ->options(SentViaEnum::asSelectArray())
                            ->enum(SentViaEnum::class),
                    ])
                    ->columns(2),

                Forms\Components\Section::make(__('Tracking Information'))
                    ->visibleOn('edit')
                    ->schema([
                        Forms\Components\TextInput::make('view_count')
                            ->label(__('View Count'))
                            ->numeric()
                            ->default(0)
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\TextInput::make('ip_address')
                            ->label(__('Last IP Address'))
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\Textarea::make('user_agent')
                            ->label(__('Last User Agent'))
                            ->disabled()
                            ->dehydrated(false)
                            ->rows(2),

                        Forms\Components\Textarea::make('failure_reason')
                            ->label(__('Failure Reason'))
                            ->helperText(__('Reason for invitation failure if status is failed'))
                            ->disabled()
                            ->dehydrated(false)
                            ->rows(2)
                            ->visible(fn ($record) => $record && $record->status === \App\Enums\InvitationStatusEnum::FAILED),

                        Forms\Components\Textarea::make('send_attempts')
                            ->label(__('Send Attempts'))
                            ->disabled()
                            ->dehydrated(false)
                            ->rows(3)
                            ->formatStateUsing(function ($state) {
                                return $state ? json_encode($state, JSON_PRETTY_PRINT) : null;
                            }),
                    ])
                    ->columns(1)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->poll('10s')
            ->columns([
                Tables\Columns\TextColumn::make('survey.title')
                    ->label(__('Survey'))
                    ->icon('heroicon-o-document-text')
                    ->searchable()
                    ->sortable(query: fn ($query, $direction) => $query->orderByJsonRelation('survey.title', $direction))
                    ->limit(30)
                    ->tooltip(function (SurveyInvitation $record): ?string {
                        return $record->survey?->getTranslation('title', 'en');
                    }),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label(__('Customer'))
                    ->icon('heroicon-o-user')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(function (SurveyInvitation $record): string {
                        return $record->customer?->name ?? __('Unknown Customer');
                    }),

                PhoneColumn::make('customer_phone')
                    ->label(__('Phone'))
                    ->searchable()
                    ->copyable()
                    ->copyMessage(__('Phone copied'))
                    ->copyMessageDuration(1500)
                    ->displayFormat(PhoneInputNumberType::NATIONAL),

                Tables\Columns\TextColumn::make('branch.name')
                    ->label(__('Branch'))
                    ->icon('heroicon-o-building-office')
                    ->formatStateUsing(fn ($record) => $record->branch?->getTranslation('name', app()->getLocale()))
                    ->searchable()
                    ->sortable(query: fn ($query, $direction) => $query->orderByJsonRelation('branch.name', $direction))
                    ->toggleable()
                    ->placeholder(__('No branch')),

                Tables\Columns\TextColumn::make('status')
                    ->color(function (SurveyInvitation $record) {
                        return match ($record->status) {
                            \App\Enums\InvitationStatusEnum::PENDING => 'warning',
                            \App\Enums\InvitationStatusEnum::SENT => 'primary',
                            \App\Enums\InvitationStatusEnum::COMPLETED => 'success',
                            \App\Enums\InvitationStatusEnum::FAILED => 'danger',
                            default => 'gray',
                        };
                    })
                    ->label(__('Status')),

                Tables\Columns\TextColumn::make('sent_via')
                    ->label(__('Sent Via')),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label(__('Expires'))
                    ->icon(function (SurveyInvitation $record): string {
                        if (! $record->expires_at) {
                            return 'heroicon-o-infinity';
                        }

                        return $record->isExpired() ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-calendar';
                    })
                    ->dateTime()
                    ->sortable()
                    ->color(function (SurveyInvitation $record): string {
                        if (! $record->expires_at) {
                            return 'gray';
                        }

                        return $record->isExpired() ? 'danger' : 'success';
                    })
                    ->formatStateUsing(function (SurveyInvitation $record): string {
                        if (! $record->expires_at) {
                            return __('Never');
                        }

                        return $record->expires_at->format('M j, Y H:i');
                    }),

                Tables\Columns\TextColumn::make('send_after')
                    ->label(__('Send After'))
                    ->icon(function (SurveyInvitation $record): string {
                        if (! $record->send_after) {
                            return 'heroicon-o-bolt';
                        }

                        return $record->send_after->isPast() ? 'heroicon-o-check' : 'heroicon-o-clock';
                    })
                    ->dateTime()
                    ->sortable()
                    ->color(function (SurveyInvitation $record): string {
                        if (! $record->send_after) {
                            return 'gray';
                        }

                        return $record->send_after->isPast() ? 'success' : 'warning';
                    })
                    ->formatStateUsing(function (SurveyInvitation $record): string {
                        if (! $record->send_after) {
                            return __('Immediately');
                        }

                        return $record->send_after->format('M j, Y H:i');
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Created'))
                    ->icon('heroicon-o-plus-circle')
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('sent_at')
                    ->label(__('Sent'))
                    ->icon('heroicon-o-paper-airplane')
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('viewed_at')
                    ->label(__('Viewed'))
                    ->icon('heroicon-o-eye')
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('public_url')
                    ->label(__('Invitation URL'))
                    ->copyable()
                    ->copyableState(fn (SurveyInvitation $record): string => $record->public_url)
                    ->copyMessage(__('URL copied to clipboard'))
                    ->limit(8)
                    ->icon('heroicon-o-clipboard'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('branch_id')
                    ->label(__('Branch'))
                    ->relationship('branch', 'name', fn ($query) => $query->orderBy('name->'.app()->getLocale()))
                    ->searchable()
                    ->preload()
                    ->placeholder(__('All Branches')),

                Tables\Filters\SelectFilter::make('customer_id')
                    ->label(__('Customer'))
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder(__('All Customers')),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label(__('Created from')),
                        Forms\Components\DatePicker::make('created_until')
                            ->label(__('Created until')),
                    ])
                    ->columns(2)
                    ->columnSpan(2)
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
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
                    ->visible(fn (SurveyInvitation $record) => in_array($record->status, [
                        InvitationStatusEnum::PENDING->value,
                        InvitationStatusEnum::QUEUED->value,
                        InvitationStatusEnum::SENT->value,
                        InvitationStatusEnum::VIEWED->value,
                    ]))
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

                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('cancel')
                        ->label(__('Cancel Selected'))
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $invitationService = app(SurveyInvitationService::class);
                            $count = 0;

                            foreach ($records as $record) {
                                if (in_array($record->status, [
                                    InvitationStatusEnum::PENDING->value,
                                    InvitationStatusEnum::QUEUED->value,
                                    InvitationStatusEnum::SENT->value,
                                    InvitationStatusEnum::VIEWED->value,
                                ])) {
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

                    Tables\Actions\BulkAction::make('extend')
                        ->label(__('Extend Selected'))
                        ->icon('heroicon-o-clock')
                        ->color('warning')
                        ->form([
                            Forms\Components\TextInput::make('extend_hours')
                                ->label(__('Extend by (hours)'))
                                ->numeric()
                                ->default(24)
                                ->minValue(1)
                                ->maxValue(168)
                                ->required(),
                        ])
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records, array $data) {
                            $invitationService = app(SurveyInvitationService::class);
                            $count = 0;

                            foreach ($records as $record) {
                                if ($record->canBeAccessed()) {
                                    try {
                                        $invitationService->extendInvitation($record, $data['extend_hours']);
                                        $count++;
                                    } catch (\Exception $e) {
                                        // Continue with other records
                                    }
                                }
                            }

                            Notification::make()
                                ->title(__('Invitations Extended'))
                                ->body(__('Extended :count invitations by :hours hours', [
                                    'count' => $count,
                                    'hours' => $data['extend_hours'],
                                ]))
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('queue_selected')
                        ->label(__('Queue Selected'))
                        ->icon('heroicon-o-arrow-path')
                        ->color('primary')
                        ->requiresConfirmation()
                        ->modalHeading(__('Queue Survey Invitations'))
                        ->modalDescription(__('This will queue the selected invitations for immediate sending via WhatsApp. Are you sure you want to continue?'))
                        ->modalSubmitActionLabel(__('Yes, Queue Them'))
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $count = 0;
                            $failed = 0;
                            $skipped = 0;

                            foreach ($records as $record) {
                                try {
                                    // Use the model's addToQueue method
                                    if ($record->addToQueue()) {
                                        $count++;
                                        \Illuminate\Support\Facades\Log::info('Successfully queued invitation via resource action', [
                                            'invitation_id' => $record->id,
                                            'customer_name' => $record->customer?->name,
                                        ]);
                                    } else {
                                        $skipped++;
                                        \Illuminate\Support\Facades\Log::info('Skipped invitation via resource action - validation failed', [
                                            'invitation_id' => $record->id,
                                            'customer_name' => $record->customer?->name,
                                        ]);
                                    }
                                } catch (\Exception $e) {
                                    $failed++;
                                    \Illuminate\Support\Facades\Log::error('Failed to queue invitation via resource action', [
                                        'invitation_id' => $record->id,
                                        'error' => $e->getMessage(),
                                    ]);
                                }
                            }

                            $message = __('Queued :count invitations', ['count' => $count]);
                            if ($skipped > 0) {
                                $message .= __(', skipped :skipped', ['skipped' => $skipped]);
                            }
                            if ($failed > 0) {
                                $message .= __(', failed :failed', ['failed' => $failed]);
                            }

                            Notification::make()
                                ->title(__('Invitations Queued'))
                                ->body($message)
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s'); // Auto-refresh every 30 seconds
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSurveyInvitations::route('/'),
            // 'create' => Pages\CreateSurveyInvitation::route('/create'),
            // 'view' => Pages\ViewSurveyInvitation::route('/{record}'),
            'edit' => Pages\EditSurveyInvitation::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::active()->count();
    }
}
