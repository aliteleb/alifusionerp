<?php

namespace Modules\Survey\Filament\Resources;

use App\Enums\SurveyStatusEnum;
use App\Enums\SurveyThemeEnum;
use App\Models\Branch;
use App\Models\Survey;
use App\Models\SurveyCategory;
use App\Models\User;
use BackedEnum;
use Filament\Actions;
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
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Modules\Survey\Filament\Actions\SendSurveyInvitationAction;
use Modules\Survey\Filament\Resources\SurveyResource\Pages;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class SurveyResource extends Resource
{
    protected static ?string $model = Survey::class;

    protected static BackedEnum|string|null $navigationIcon = Heroicon::ClipboardDocumentList;

    public static function getNavigationGroup(): ?string
    {
        return __('Survey Management');
    }

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'title';

    public static function getPluralLabel(): string
    {
        return __('Surveys');
    }

    public static function getLabel(): string
    {
        return __('Survey');
    }

    public static function getNavigationLabel(): string
    {
        return __('Surveys');
    }

    public static function canAccess(): bool
    {
        /** @var User $user */
        $user = Filament::getCurrentPanel()->auth()->user();

        return $user ? $user->can('access_surveys') : false;
    }

    public static function canViewAny(): bool
    {
        /** @var User $user */
        $user = Filament::getCurrentPanel()->auth()->user();

        return $user ? $user->can('view_surveys') : false;
    }

    public static function canView($record): bool
    {
        /** @var User $user */
        $user = Filament::getCurrentPanel()->auth()->user();

        return $user ? $user->can('view_surveys') : false;
    }

    public static function canCreate(): bool
    {
        /** @var User $user */
        $user = Filament::getCurrentPanel()->auth()->user();

        return $user ? $user->can('create_surveys') : false;
    }

    public static function canEdit($record): bool
    {
        /** @var User $user */
        $user = Filament::getCurrentPanel()->auth()->user();

        return $user ? $user->can('edit_surveys') : false;
    }

    public static function canDelete($record): bool
    {
        /** @var User $user */
        $user = Filament::getCurrentPanel()->auth()->user();

        return $user ? $user->can('delete_surveys') : false;
    }

    public static function form(Schema $schema): Schema
    {
        $qrComponent = null;

        if (class_exists(\LaraZeus\Qr\Components\Qr::class)) {
            $qrComponent = \LaraZeus\Qr\Components\Qr::make('qr_code_url')
                ->optionsColumn('qr_options')
                ->asSlideOver()
                ->actionIcon('heroicon-o-qr-code')
                ->afterStateHydrated(function ($state, callable $set, $record) {
                    if ($record) {
                        $set('qr_code_url', $record->getPublicAccessUrl());
                    }
                })
                ->visible(fn ($get, $record): bool => (bool) $get('public_access_enabled') || ($record && $record->public_access_enabled))
                ->columnSpanFull();
        }

        return $schema
            ->columns(3)
            ->components([
                Group::make()
                    ->columnSpan(2)
                    ->schema([
                        Section::make(__('Survey Information'))
                            ->description(__('Basic survey details with multi-language support'))
                            ->icon('heroicon-o-clipboard-document-list')
                            ->schema([
                                Translate::make()
                                    ->schema([
                                        Forms\Components\TextInput::make('title')
                                            ->label(__('Title'))
                                            ->required()
                                            ->maxLength(200),
                                    ])
                                    ->locales(appLocales())
                                    ->columnSpanFull(),

                                Forms\Components\TextInput::make('slug')
                                    ->label(__('URL Slug'))
                                    ->required()
                                    ->unique(Survey::class, 'slug', ignoreRecord: true)
                                    ->helperText(__('URL-friendly identifier'))
                                    ->columnSpanFull(),

                                Translate::make()
                                    ->hidden()
                                    ->schema([
                                        Forms\Components\Textarea::make('description')
                                            ->label(__('Description'))
                                            ->rows(3),
                                    ])
                                    ->locales(appLocales())
                                    ->columnSpanFull(),
                            ])
                            ->collapsible()
                            ->compact(),
                    ]),
                Group::make()
                    ->columnSpan(1)
                    ->schema([
                        Section::make(__('Survey Settings'))
                            ->description(__('Configure survey behavior and assignment'))
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([

                                Forms\Components\Select::make('survey_category_id')
                                    ->label(__('Category'))
                                    ->options(
                                        SurveyCategory::query()
                                            ->where('is_active', true)
                                            ->orderBy('id')
                                            ->get()
                                            ->mapWithKeys(fn (SurveyCategory $category) => [
                                                $category->id => $category->getTranslation('name', app()->getLocale()),
                                            ])
                                    )
                                    ->required()
                                    ->searchable()
                                    ->columnSpan(1),

                                Forms\Components\Select::make('status')
                                    ->label(__('Status'))
                                    ->options(SurveyStatusEnum::class)
                                    ->default(SurveyStatusEnum::DRAFT)
                                    ->required()
                                    ->native(false)
                                    ->columnSpan(1),

                                Forms\Components\Select::make('branch_id')
                                    ->label(__('Branch'))
                                    ->options(Branch::pluck('name', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->placeholder(__('All Branches'))
                                    ->helperText(__('Leave empty to make survey available for all branches')),

                                Forms\Components\TextInput::make('max_responses')
                                    ->label(__('Maximum Responses'))
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(10000)
                                    ->placeholder(__('Unlimited'))
                                    ->helperText(__('Leave empty for unlimited responses'))
                                    ->suffix(__('responses'))
                                    ->columnSpan(1),
                            ])
                            ->collapsible()
                            ->compact()
                            ->columns(1),
                    ]),

                Section::make(__('Appearance & Theme'))
                    ->description(__('Customize the visual appearance of your survey'))
                    ->icon('heroicon-o-swatch')
                    ->schema([
                        Forms\Components\ToggleButtons::make('theme')
                            ->label(__('Survey Theme'))
                            ->options(SurveyThemeEnum::class)
                            ->default(SurveyThemeEnum::DEFAULT)
                            ->inline()
                            ->required()
                            ->helperText(__('Choose the visual theme for your survey')),

                        Forms\Components\ColorPicker::make('theme_color')
                            ->label(__('Primary Color'))
                            ->default('#3B82F6')
                            ->helperText(__('Primary color used throughout the survey')),
                    ])
                    ->collapsible()
                    ->compact()
                    ->columnSpanFull(),

                Section::make(__('Public Access'))
                    ->hidden()
                    ->description(__('Enable public access to this survey via a permanent link'))
                    ->icon('heroicon-o-globe-alt')
                    ->schema(array_filter([
                        Forms\Components\Toggle::make('public_access_enabled')
                            ->label(__('Enable Public Access'))
                            ->helperText(__('When enabled, anyone can access this survey via a permanent link without invitation'))
                            ->reactive()
                            ->columnSpanFull(),

                        Forms\Components\Placeholder::make('public_link')
                            ->label(__('Public Survey Link'))
                            ->content(function ($record) {
                                if ($record && $record->public_access_enabled) {
                                    $url = $record->getPublicAccessUrl();

                                    return new \Illuminate\Support\HtmlString(
                                        '<a href="'.$url.'" target="_blank" class="text-primary-600 hover:underline">'.$url.'</a>'
                                    );
                                }

                                return __('Enable public access to generate link');
                            })
                            ->visible(fn ($get, $record): bool => (bool) $get('public_access_enabled') || ($record && $record->public_access_enabled))
                            ->columnSpanFull(),

                        $qrComponent,
                    ], fn ($component) => $component !== null))
                    ->collapsible()
                    ->compact()
                    ->columnSpanFull(),

                Section::make(__('WhatsApp Message Template'))
                    ->description(__('Customize the WhatsApp invitation message with variables'))
                    ->icon('heroicon-o-chat-bubble-bottom-center-text')
                    ->schema([
                        Translate::make()
                            ->schema([
                                Forms\Components\MarkdownEditor::make('whatsapp_message')
                                    ->label(__('WhatsApp Message Template'))
                                    ->placeholder(__('Hello {customer_name}! We invite you to participate in our survey: {survey_title}. Please click the link: {invitation_url}. From: {branch_name}'))
                                    ->helperText(__('Available variables: {customer_name}, {survey_title}, {invitation_url}, {branch_name}, {company_name}'))
                                    ->toolbarButtons([
                                        'bold',
                                        'italic',
                                        'undo',
                                        'redo',
                                    ])
                                    ->required(fn ($get): bool => (bool) $get('whatsapp_enabled')),
                            ])
                            ->locales(appLocales())
                            ->columnSpanFull()
                            ->visible(fn ($get): bool => (bool) $get('whatsapp_enabled')),
                    ])
                    ->collapsible()
                    ->compact()
                    ->columnSpanFull(),

                Section::make(__('Bad Rating Alerts'))
                    ->description(__('Configure phone numbers to receive WhatsApp alerts when customers give low ratings (2 stars or less)'))
                    ->icon('heroicon-o-exclamation-triangle')
                    ->schema([
                        Forms\Components\TagsInput::make('bad_rating_alert_phones')
                            ->label(__('Alert Phone Numbers'))
                            ->placeholder(__('Enter phone numbers (e.g., +1234567890, +447123456789)'))
                            ->helperText(__('Enter phone numbers in international format (+COUNTRYCODE followed by number) that should receive WhatsApp alerts when customers give ratings of 2 stars or less. One number per tag.'))
                            ->separator(',')
                            ->extraAttributes(['dir' => 'ltr'])
                            ->formatStateUsing(function (?array $state): array {
                                if (! $state) {
                                    return [];
                                }

                                return array_map(function ($phone) {
                                    // Format display: ensure + prefix for international format
                                    $cleaned = preg_replace('/[^0-9]/', '', $phone);

                                    return '+'.$cleaned;
                                }, $state);
                            })
                            ->dehydrateStateUsing(function (?array $state): array {
                                if (! $state) {
                                    return [];
                                }

                                return array_map(function ($phone) {
                                    // Store clean numbers only
                                    return preg_replace('/[^0-9]/', '', $phone);
                                }, $state);
                            })
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->compact()
                    ->columnSpanFull(),

                Section::make(__('Welcome & Thank You Messages'))
                    ->hidden()
                    ->description(__('Customize messages shown to survey participants'))
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->schema([
                        Translate::make()
                            ->schema([
                                Forms\Components\Textarea::make('welcome_message')
                                    ->label(__('Welcome Message'))
                                    ->rows(3)
                                    ->placeholder(__('Welcome! Please take a few minutes to complete this survey.')),
                            ])
                            ->locales(appLocales())
                            ->columnSpanFull(),

                        Translate::make()
                            ->schema([
                                Forms\Components\Textarea::make('thank_you_message')
                                    ->label(__('Thank You Message'))
                                    ->rows(3)
                                    ->placeholder(__('Thank you for completing our survey!')),
                            ])
                            ->locales(appLocales())
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->compact(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label(__('Title'))
                    ->icon('heroicon-o-clipboard-document-list')
                    ->iconColor('primary')
                    ->formatStateUsing(fn ($record) => $record->getTranslation('title', app()->getLocale()))
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->wrap()
                    ->description(fn (Survey $record): string => $record->slug)
                    ->grow(),

                Tables\Columns\TextColumn::make('surveyCategory.name')
                    ->label(__('Category'))
                    ->icon('heroicon-o-tag')
                    ->formatStateUsing(fn ($record) => $record->surveyCategory?->getTranslation('name', app()->getLocale()))
                    ->badge()
                    ->color(fn ($record): string => $record->surveyCategory?->color ?? 'gray')
                    ->sortable(query: fn ($query, $direction) => $query->orderByJsonRelation('surveyCategory.name', $direction)),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('branch.name')
                    ->label(__('Branch'))
                    ->icon('heroicon-o-building-office-2')
                    ->badge()
                    ->color('primary')
                    ->placeholder(__('All Branches'))
                    ->sortable(query: fn ($query, $direction) => $query->orderByJsonRelation('branch.name', $direction)),

                Tables\Columns\TextColumn::make('theme')
                    ->label(__('Theme'))
                    ->icon('heroicon-o-paint-brush')
                    ->badge()
                    ->color(fn (Survey $record): string => $record->theme?->getColor() ?? 'gray')
                    ->formatStateUsing(fn (Survey $record): string => $record->theme?->getLabel() ?? __('Default'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_responses')
                    ->label(__('Responses'))
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->alignCenter()
                    ->tooltip(fn (Survey $record): string => __('Average Rating').': '.($record->average_rating ? number_format($record->average_rating, 2).'⭐' : __('N/A'))),

                Tables\Columns\TextColumn::make('average_rating')
                    ->label(__('Rating'))
                    ->icon('heroicon-o-star')
                    ->badge()
                    ->color(fn ($state): string => match (true) {
                        $state >= 4.5 => 'success',
                        $state >= 3.5 => 'info',
                        $state >= 2.5 => 'warning',
                        $state > 0 => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state): string => $state ? number_format($state, 2).'⭐' : __('N/A'))
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(),

                Tables\Columns\ToggleColumn::make('public_access_enabled')
                    ->label(__('Public'))
                    ->onIcon('heroicon-o-globe-alt')
                    ->offIcon('heroicon-o-lock-closed')
                    ->onColor('success')
                    ->offColor('gray')
                    ->tooltip(fn (bool $state): string => $state ? __('Public access enabled') : __('Private survey'))
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->icon('heroicon-o-calendar')
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options(SurveyStatusEnum::class)
                    ->placeholder(__('All Statuses'))
                    ->native(false),

                Tables\Filters\SelectFilter::make('survey_category_id')
                    ->label(__('Category'))
                    ->relationship('surveyCategory', 'name')
                    ->searchable()
                    ->placeholder(__('All Categories')),

                Tables\Filters\SelectFilter::make('branch_id')
                    ->label(__('Branch'))
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->placeholder(__('All Branches')),

                Tables\Filters\SelectFilter::make('theme')
                    ->label(__('Theme'))
                    ->options(SurveyThemeEnum::class)
                    ->placeholder(__('All Themes')),
            ])
            ->recordActions([
                SendSurveyInvitationAction::make(),

                Actions\Action::make('open_public_link')
                    ->label(__('Public Link'))
                    ->icon('heroicon-o-globe-alt')
                    ->color('info')
                    ->url(fn (Survey $record): ?string => $record->getPublicAccessUrl())
                    ->openUrlInNewTab()
                    ->visible(fn (Survey $record): bool => $record->public_access_enabled)
                    ->tooltip(__('Open public survey link')),

                Actions\Action::make('design_qr_code')
                    ->label(__('Design QR'))
                    ->icon('heroicon-o-qr-code')
                    ->color('success')
                    ->fillForm(function (Survey $record): array {
                        $defaultOptions = \LaraZeus\Qr\Facades\Qr::getDefaultOptions();
                        $savedOptions = $record->qr_options ?? [];

                        // Merge saved options with defaults to ensure all required fields exist
                        $options = array_merge($defaultOptions, $savedOptions);

                        // Ensure gradient_type has a default value
                        if (isset($options['hasGradient']) && $options['hasGradient'] && empty($options['gradient_type'])) {
                            $options['gradient_type'] = 'vertical';
                        }

                        return [
                            'qr-data' => $record->getPublicAccessUrl(),
                            'qr-options' => $options,
                        ];
                    })
                    ->form(fn () => \LaraZeus\Qr\Facades\Qr::getFormSchema('qr-data', 'qr-options'))
                    ->action(function (array $data, Survey $record): void {
                        // Clean and validate QR options before saving
                        $options = $data['qr-options'];

                        // Fix gradient_type if gradient is enabled
                        if (isset($options['hasGradient']) && $options['hasGradient']) {
                            if (empty($options['gradient_type'])) {
                                $options['gradient_type'] = 'vertical';
                            }
                            // Ensure gradient colors exist
                            if (empty($options['gradient_form'])) {
                                $options['gradient_form'] = 'rgb(69, 179, 157)';
                            }
                            if (empty($options['gradient_to'])) {
                                $options['gradient_to'] = 'rgb(241, 148, 138)';
                            }
                        }

                        // Fix eye style if eye config is enabled
                        if (isset($options['hasEyeColor']) && $options['hasEyeColor']) {
                            if (empty($options['eye_style'])) {
                                $options['eye_style'] = 'square';
                            }
                        }

                        $record->update([
                            'qr_options' => $options,
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title(__('QR Code design saved'))
                            ->body(__('Your custom QR code design has been saved successfully'))
                            ->send();
                    })
                    ->modalHeading(fn (Survey $record): string => __('Design QR Code').': '.$record->getTranslation('title', app()->getLocale()))
                    ->modalDescription(__('Customize colors, gradients, eye styles, and add your logo'))
                    ->modalSubmitActionLabel(__('Save Design'))
                    ->modalWidth('5xl')
                    ->slideOver()
                    ->visible(fn (Survey $record): bool => $record->public_access_enabled)
                    ->tooltip(__('Design QR code with colors, gradients, shapes, and logo')),

                Actions\EditAction::make()
                    ->color('warning')
                    ->iconButton(),
                Actions\DeleteAction::make()
                    ->color('danger')
                    ->iconButton(),
                Actions\RestoreAction::make()
                    ->color('success')
                    ->iconButton(),
            ])
            ->toolbarActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                    Actions\RestoreBulkAction::make(),
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
            SurveyResource\RelationManagers\QuestionsRelationManager::class,
            // SurveyResource\RelationManagers\InvitationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSurveys::route('/'),
            'create' => Pages\CreateSurvey::route('/create'),
            'edit' => Pages\EditSurvey::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->with(['surveyCategory', 'branch', 'creator']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'slug', 'description'];
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            __('Category') => $record->surveyCategory?->getTranslation('name', app()->getLocale()),
            __('Status') => $record->status,
            __('Branch') => $record->branch?->name ?? __('All Branches'),
        ];
    }
}
