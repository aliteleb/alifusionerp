<?php

namespace Modules\Survey\Filament\Resources;

use App\Enums\SurveyResponseStatusEnum;
use App\Models\Customer;
use App\Models\SurveyResponse;
use App\Models\User;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Modules\Survey\Filament\Resources\SurveyResponseResource\Pages;
use Mokhosh\FilamentRating\Components\Rating;
use Mokhosh\FilamentRating\RatingTheme;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;
use Ysfkaya\FilamentPhoneInput\PhoneInputNumberType;

class SurveyResponseResource extends Resource
{
    protected static ?string $model = SurveyResponse::class;

    protected static BackedEnum|string|null $navigationIcon = Heroicon::ChatBubbleLeftEllipsis;

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'customer.name';

    public static function getNavigationGroup(): ?string
    {
        return __('Survey Management');
    }

    public static function getPluralLabel(): string
    {
        return __('Survey Responses');
    }

    public static function getLabel(): string
    {
        return __('Survey Response');
    }

    public static function getNavigationLabel(): string
    {
        return __('Responses');
    }

    public static function canAccess(): bool
    {
        /** @var User $user */
        $user = Filament::getCurrentPanel()->auth()->user();

        return $user ? $user->can('access_responses') : false;
    }

    public static function canViewAny(): bool
    {
        /** @var User $user */
        $user = Filament::getCurrentPanel()->auth()->user();

        return $user ? $user->can('view_responses') : false;
    }

    public static function canView($record): bool
    {
        /** @var User $user */
        $user = Filament::getCurrentPanel()->auth()->user();

        return $user ? $user->can('view_responses') : false;
    }

    public static function canCreate(): bool
    {
        /** @var User $user */
        $user = Filament::getCurrentPanel()->auth()->user();

        return $user ? $user->can('create_responses') : false;
    }

    public static function canEdit($record): bool
    {
        /** @var User $user */
        $user = Filament::getCurrentPanel()->auth()->user();

        return $user ? $user->can('edit_responses') : false;
    }

    public static function canDelete($record): bool
    {
        /** @var User $user */
        $user = Filament::getCurrentPanel()->auth()->user();

        return $user ? $user->can('delete_responses') : false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make()
                    ->columnSpan(2)
                    ->schema([
                        Section::make(__('Response Details'))
                            ->columns(2)
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
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (callable $set) {
                                        $set('question_responses', []);
                                    }),

                                Forms\Components\Select::make('customer_id')
                                    ->label(__('Customer'))
                                    ->relationship('customer', 'name')
                                    ->searchableBy(
                                        ['name', 'phone', 'email'],
                                        \App\Models\Customer::class,
                                        labelFormatter: fn ($record) => $record->name.($record->phone ? ' ('.$record->phone.')' : '')
                                    )->preload()
                                    ->required()
                                    ->createOptionForm([
                                        Section::make(__('Primary Details'))
                                            ->columns(2)
                                            ->schema([
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
                                                    ->unique(Customer::class, 'phone', ignoreRecord: true)
                                                    ->defaultCountry('IQ')
                                                    ->displayNumberFormat(PhoneInputNumberType::NATIONAL)
                                                    ->inputNumberFormat(PhoneInputNumberType::INTERNATIONAL),

                                                Forms\Components\TextInput::make('email')
                                                    ->label(__('Email Address'))
                                                    ->email()
                                                    ->unique('customers', 'email')
                                                    ->maxLength(255),
                                            ]),

                                        Section::make(__('Additional Details'))
                                            ->columns(3)
                                            ->schema([
                                                Forms\Components\Select::make('gender_id')
                                                    ->label(__('Gender'))
                                                    ->relationship(
                                                        name: 'gender',
                                                        titleAttribute: 'name',
                                                        modifyQueryUsing: fn (Builder $query) => $query->orderByRaw("name->>'".app()->getLocale()."' ASC")
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
                                                        modifyQueryUsing: fn ($query) => $query->orderBy('name->'.app()->getLocale())
                                                    )
                                                    ->required()
                                                    ->searchable()
                                                    ->preload(),
                                            ]),

                                        Section::make(__('Contact Information'))
                                            ->columns(1)
                                            ->schema([
                                                Forms\Components\Textarea::make('address')
                                                    ->label(__('Address'))
                                                    ->rows(2),

                                                Forms\Components\Textarea::make('notes')
                                                    ->label(__('Notes'))
                                                    ->rows(2),
                                            ]),
                                    ])
                                    ->createOptionUsing(function (array $data): int {
                                        $data['created_by'] = Auth::id();
                                        $data['updated_by'] = Auth::id();
                                        $data['visit_time'] = $data['visit_time'] ?? now();

                                        $customer = \App\Models\Customer::create($data);

                                        return $customer->id;
                                    })
                                    ->createOptionModalHeading(__('Create New Customer'))
                                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->name.($record->phone ? ' ('.$record->phone.')' : '')),
                            ]),

                        Section::make(__('Question Responses'))
                            ->icon('heroicon-o-chart-pie')
                            ->compact()
                            ->schema(function (callable $get) {
                                $surveyId = $get('survey_id');

                                if (! $surveyId) {
                                    return [
                                        Forms\Components\Placeholder::make('no_survey_selected')
                                            ->label(__('Please select a survey first'))
                                            ->content(__('Choose a survey above to see its questions.')),
                                    ];
                                }

                                $survey = \App\Models\Survey::with('questions')->find($surveyId);

                                if (! $survey || $survey->questions->isEmpty()) {
                                    return [
                                        Forms\Components\Placeholder::make('no_questions')
                                            ->label(__('No questions found'))
                                            ->content(__('This survey has no questions configured.')),
                                    ];
                                }

                                $questionComponents = [];

                                foreach ($survey->questions as $question) {
                                    $questionText = $question->getTranslation('question_text', app()->getLocale());
                                    $questionLabel = __('Question.')."{$question->order}: {$questionText}";
                                    $questionType = $question->question_type->value; // Get enum value

                                    // Create different input types based on question type
                                    switch ($questionType) {
                                        case 'text':
                                            $questionComponents[] = Forms\Components\Textarea::make('question_responses.'.$question->id)
                                                ->label($questionLabel)
                                                // ->helperText($question->getTranslation('description', app()->getLocale()))
                                                ->placeholder($question->getTranslation('placeholder', app()->getLocale()))
                                                ->required($question->is_required)
                                                ->rows(3)
                                                ->columnSpanFull();
                                            break;

                                        case 'rating':
                                            $min = $question->min_value ?? 1;
                                            $max = $question->max_value ?? 5;

                                            $questionComponents[] = Rating::make('question_responses.'.$question->id)
                                                ->label($questionLabel)
                                                // ->helperText($question->getTranslation('description', app()->getLocale()) . ' (' . __('Rating from :min to :max', ['min' => $min, 'max' => $max]) . ')')
                                                ->stars($max)
                                                ->theme(RatingTheme::Simple)
                                                ->color('warning')
                                                ->live()
                                                ->required($question->is_required)
                                                ->columnSpanFull();

                                            // Add reason field for low ratings (2 stars or less)
                                            $questionComponents[] = Forms\Components\TextInput::make('question_responses.reason_'.$question->id)
                                                ->label(__('Reason for low rating'))
                                                ->placeholder(__('Please explain why you gave this rating...'))
                                                ->required()
                                                ->hidden(function ($get, $set) use ($question) {
                                                    if (is_null($get('question_responses.'.$question->id))) {
                                                        $set('question_responses.'.$question->id, 3);
                                                    }
                                                    $rating = $get('question_responses.'.$question->id) ?? 5;

                                                    return $rating > 2;
                                                })
                                                ->live()
                                                ->helperText(__('Required when rating is 2 stars or less'))
                                                ->columnSpanFull();
                                            break;

                                        default:
                                            // Default to textarea for unknown types (Debug: showing type)
                                            $questionComponents[] = Forms\Components\Textarea::make('question_responses.'.$question->id)
                                                ->label($questionLabel.' (TYPE: '.$questionType.')')
                                                ->helperText($question->getTranslation('description', app()->getLocale()))
                                                ->placeholder($question->getTranslation('placeholder', app()->getLocale()))
                                                ->required($question->is_required)
                                                ->columnSpanFull();
                                            break;
                                    }
                                }

                                return $questionComponents;
                            }),
                    ]),
                Group::make()
                    ->columnSpan(1)
                    ->schema([
                                Section::make(__('Response Status'))
                                    ->icon('heroicon-o-flag')
                                    ->schema([
                                        Forms\Components\ToggleButtons::make('status')
                                            ->label(__('Status'))
                                            ->options(SurveyResponseStatusEnum::class)
                                            ->default(SurveyResponseStatusEnum::DRAFT)
                                            ->inline()
                                            ->required()
                                            ->columnSpanFull(),
                                    ]),

                                Section::make(__('More Information'))
                                    ->icon('heroicon-o-chart-pie')
                                    ->compact()
                                    ->collapsed()
                                    ->columns(2)
                                    ->schema([
                                        Forms\Components\Hidden::make('started_at')
                                            ->label(__('Started At'))
                                            ->default(now())
                                            ->required()
                                            ->columnSpanFull(),
                                        Forms\Components\Toggle::make('is_complete')
                                            ->label(__('Complete'))
                                            ->default(true),
                                        Forms\Components\Toggle::make('is_verified')
                                            ->label(__('Verified'))
                                            ->default(true),
                                        Forms\Components\Textarea::make('feedback')
                                            ->label(__('Additional Feedback'))
                                            ->rows(3)
                                            ->columnSpanFull(),
                                        Forms\Components\Textarea::make('notes')
                                            ->label(__('Internal Notes'))
                                            ->rows(2)
                                            ->helperText(__('Internal notes - not visible to customer'))
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                // Response Statistics (read-only)
                // Forms\Components\Section::make(__('Response Statistics'))
                //     ->description(__('Automatically calculated statistics'))
                //     ->icon('heroicon-o-chart-bar')
                //     ->schema([
                //         Forms\Components\Grid::make(3)
                //             ->schema([
                //                 Forms\Components\TextInput::make('total_questions')
                //                     ->label(__('Total Questions'))
                //                     ->numeric()
                //                     ->readonly(),

                //                 Forms\Components\TextInput::make('answered_questions')
                //                     ->label(__('Answered Questions'))
                //                     ->numeric()
                //                     ->readonly(),

                //                 Forms\Components\TextInput::make('skipped_questions')
                //                     ->label(__('Skipped Questions'))
                //                     ->numeric()
                //                     ->readonly(),
                //             ]),

                //         Forms\Components\Grid::make(2)
                //             ->schema([
                //                 Forms\Components\TextInput::make('completion_percentage')
                //                     ->label(__('Completion %'))
                //                     ->numeric()
                //                     ->suffix('%')
                //                     ->readonly(),

                //                 Forms\Components\TextInput::make('average_rating')
                //                     ->label(__('Average Rating'))
                //                     ->numeric()
                //                     ->step(0.1)
                //                     ->readonly(),
                //             ]),
                //     ])
                //     ->collapsible()
                //     ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('survey.title')
                    ->label(__('Survey'))
                    ->icon('heroicon-o-document-text')
                    ->formatStateUsing(fn ($record) => $record->survey?->getTranslation('title', app()->getLocale()))
                    ->searchable()
                    ->sortable(query: fn ($query, $direction) => $query->orderByJsonRelation('survey.title', $direction))
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->survey?->getTranslation('title', app()->getLocale())),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label(__('Customer'))
                    ->icon('heroicon-o-user')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('customer.phone')
                    ->label(__('Phone'))
                    ->icon('heroicon-o-phone')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('branch.name')
                    ->label(__('Branch'))
                    ->icon('heroicon-o-building-office')
                    ->formatStateUsing(fn ($record) => $record->branch?->getTranslation('name', app()->getLocale()))
                    ->searchable()
                    ->sortable(query: fn ($query, $direction) => $query->orderByJsonRelation('branch.name', $direction))
                    ->toggleable()
                    ->placeholder(__('No branch')),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->color(fn ($state) => $state?->getColor())
                    ->icon(fn ($state) => $state?->getIcon())
                    ->formatStateUsing(fn ($state) => $state?->getLabel())
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('started_at')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label(__('Started'))
                    ->icon('heroicon-o-play')
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->tooltip(fn ($record) => $record->started_at?->format('Y-m-d H:i:s')),

                Tables\Columns\TextColumn::make('completed_at')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label(__('Completed'))
                    ->icon('heroicon-o-check')
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->placeholder(__('Not completed'))
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_complete')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label(__('Complete'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_verified')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label(__('Verified'))
                    ->boolean()
                    ->trueIcon('heroicon-o-shield-check')
                    ->falseIcon('heroicon-o-shield-exclamation')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('completion_percentage')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label(__('Progress'))
                    ->icon('heroicon-o-chart-bar')
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state >= 100 => 'success',
                        $state >= 75 => 'info',
                        $state >= 50 => 'warning',
                        default => 'danger',
                    })
                    ->formatStateUsing(fn ($state) => $state.'%')
                    ->sortable(),

                Tables\Columns\TextColumn::make('average_rating')
                    ->label(__('Avg Rating'))
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 1) : '-')
                    ->sortable()
                    ->tooltip(function ($record) {
                        // Show reasons if average rating is 2 or less
                        if ($record->average_rating && $record->average_rating <= 2) {
                            $reasons = $record->questionResponses()
                                ->whereNotNull('reason_for_rating')
                                ->where('rating_value', '<=', 2)
                                ->pluck('reason_for_rating')
                                ->filter()
                                ->toArray();

                            return ! empty($reasons) ? __('Reason').': '.implode('; ', $reasons) : null;
                        }

                        return null;
                    })
                    ->toggleable(),

                Tables\Columns\TextColumn::make('answered_questions')
                    ->label(__('Answered'))
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn ($state, $record) => $state.'/'.$record->total_questions)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Created In'))
                    ->icon('heroicon-o-calendar')
                    ->date()
                    ->sortable(),

            ])
            ->filters([
                Tables\Filters\SelectFilter::make('survey_id')
                    ->label(__('Survey'))
                    ->options(function () {
                        return \App\Models\Survey::orderedByTitle()
                            ->get()
                            ->mapWithKeys(function ($survey) {
                                return [$survey->id => $survey->getTranslation('title', app()->getLocale())];
                            });
                    })
                    ->searchable()
                    ->placeholder(__('All Surveys')),

                Tables\Filters\SelectFilter::make('customer_id')
                    ->label(__('Customer'))
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->placeholder(__('All Customers')),

                Tables\Filters\SelectFilter::make('branch_id')
                    ->label(__('Branch'))
                    ->relationship('branch', 'name', fn ($query) => $query->orderBy('name->'.app()->getLocale()))
                    ->searchable()
                    ->preload()
                    ->placeholder(__('All Branches')),

                Tables\Filters\SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options(SurveyResponseStatusEnum::class)
                    ->placeholder(__('All Statuses')),

                Tables\Filters\TernaryFilter::make('is_complete')
                    ->label(__('Completion Status'))
                    ->placeholder(__('All Responses'))
                    ->trueLabel(__('Complete Only'))
                    ->falseLabel(__('Incomplete Only')),

                Tables\Filters\TernaryFilter::make('is_verified')
                    ->label(__('Verification Status'))
                    ->placeholder(__('All Responses'))
                    ->trueLabel(__('Verified Only'))
                    ->falseLabel(__('Unverified Only')),

                Tables\Filters\Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('started_from')
                            ->label(__('Started From')),
                        Forms\Components\DatePicker::make('started_until')
                            ->label(__('Started Until')),
                    ])->columns(2)->columnSpan(2)
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['started_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('started_at', '>=', $date),
                            )
                            ->when(
                                $data['started_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('started_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['started_from'] ?? null) {
                            $indicators[] = 'Started from: '.$data['started_from'];
                        }

                        if ($data['started_until'] ?? null) {
                            $indicators[] = 'Started until: '.$data['started_until'];
                        }

                        return $indicators;
                    }),
            ])
            ->actions([
                \Filament\Actions\EditAction::make()
                    ->color('warning')
                    ->iconButton()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['updated_by'] = Auth::id();

                        return $data;
                    }),
                \Filament\Actions\DeleteAction::make()
                    ->color('danger')
                    ->iconButton(),
                \Filament\Actions\RestoreAction::make()
                    ->color('success')
                    ->iconButton(),
                \Filament\Actions\ForceDeleteAction::make()
                    ->color('danger')
                    ->iconButton(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                    \Filament\Actions\RestoreBulkAction::make(),
                    \Filament\Actions\ForceDeleteBulkAction::make(),
                    \Filament\Actions\BulkAction::make('mark_verified')
                        ->label(__('Mark as Verified'))
                        ->icon('heroicon-o-shield-check')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each(fn ($record) => $record->update([
                                'is_verified' => true,
                                'updated_by' => Auth::id(),
                            ]));
                        })
                        ->requiresConfirmation(),
                    \Filament\Actions\BulkAction::make('mark_unverified')
                        ->label(__('Mark as Unverified'))
                        ->icon('heroicon-o-shield-exclamation')
                        ->color('warning')
                        ->action(function ($records) {
                            $records->each(fn ($record) => $record->update([
                                'is_verified' => false,
                                'updated_by' => Auth::id(),
                            ]));
                        })
                        ->requiresConfirmation(),
                ])
                    ->label(__('Actions'))
                    ->color('primary')
                    ->icon('heroicon-o-ellipsis-horizontal'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            SurveyResponseResource\RelationManagers\QuestionResponsesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSurveyResponses::route('/'),
            // 'create' => Pages\CreateSurveyResponse::route('/create'),
            'view' => Pages\ViewSurveyResponse::route('/{record}'),
            'edit' => Pages\EditSurveyResponse::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['survey', 'customer', 'questionResponses']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['id', 'customer.name', 'customer.phone', 'survey.title'];
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            __('Survey') => $record->survey?->getTranslation('title', app()->getLocale()),
            __('Customer') => $record->customer?->name,
            __('Status') => $record->is_complete ? __('Complete') : __('Incomplete'),
            __('Progress') => $record->completion_percentage.'%',
        ];
    }

    public static function canDeleteAny(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        return $user?->can('delete_survey_responses') ?? false;
    }
}
