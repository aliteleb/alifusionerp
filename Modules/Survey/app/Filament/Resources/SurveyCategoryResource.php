<?php

namespace Modules\Survey\Filament\Resources;

use App\Models\SurveyCategory;
use App\Models\User;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Modules\Survey\Filament\Resources\SurveyCategoryResource\Pages;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class SurveyCategoryResource extends Resource
{
    protected static ?string $model = SurveyCategory::class;

    protected static BackedEnum|string|null $navigationIcon = Heroicon::Squares2x2;

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): ?string
    {
        return __('Survey Management');
    }

    public static function getPluralLabel(): string
    {
        return __('Survey Categories');
    }

    public static function getLabel(): string
    {
        return __('Survey Category');
    }

    public static function getNavigationLabel(): string
    {
        return __('Categories');
    }

    public static function canAccess(): bool
    {
        /** @var User $user */
        $user = Filament::getCurrentPanel()->auth()->user();

        return $user ? $user->can('access_survey_categories') : false;
    }

    public static function canViewAny(): bool
    {
        /** @var User $user */
        $user = Filament::getCurrentPanel()->auth()->user();

        return $user ? $user->can('view_survey_categories') : false;
    }

    public static function canView($record): bool
    {
        /** @var User $user */
        $user = Filament::getCurrentPanel()->auth()->user();

        return $user ? $user->can('view_survey_categories') : false;
    }

    public static function canCreate(): bool
    {
        /** @var User $user */
        $user = Filament::getCurrentPanel()->auth()->user();

        return $user ? $user->can('create_survey_categories') : false;
    }

    public static function canEdit($record): bool
    {
        /** @var User $user */
        $user = Filament::getCurrentPanel()->auth()->user();

        return $user ? $user->can('edit_survey_categories') : false;
    }

    public static function canDelete($record): bool
    {
        /** @var User $user */
        $user = Filament::getCurrentPanel()->auth()->user();

        return $user ? $user->can('delete_survey_categories') : false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Section::make(__('Category Information'))
                    ->description(__('Define the survey category with multi-language support'))
                    ->icon('heroicon-o-tag')
                    ->schema([
                        Translate::make()
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label(__('Name'))
                                    ->required()
                                    ->maxLength(100),
                            ])
                            ->locales(appLocales())
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('slug')
                            ->label(__('URL Slug'))
                            ->required()
                            ->maxLength(100)
                            ->unique(SurveyCategory::class, 'slug', ignoreRecord: true)
                            ->rules(['alpha_dash'])
                            ->helperText(__('URL-friendly identifier (auto-generated from English name)'))
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
                    ->persistCollapsed()
                    ->compact(),

                Forms\Components\Section::make(__('Display Settings'))
                    ->description(__('Configure how this category appears in the interface'))
                    ->icon('heroicon-o-paint-brush')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('icon')
                                    ->label(__('Icon'))
                                    ->options([
                                        'heroicon-o-face-smile' => __('Satisfaction'),
                                        'heroicon-o-chat-bubble-left-right' => __('Feedback'),
                                        'heroicon-o-star' => __('Rating'),
                                        'heroicon-o-clipboard-document-list' => __('Questionnaire'),
                                        'heroicon-o-chart-bar' => __('Poll'),
                                        'heroicon-o-shield-check' => __('Quality'),
                                        'heroicon-o-academic-cap' => __('Research'),
                                        'heroicon-o-heart' => __('Experience'),
                                        'heroicon-o-puzzle-piece' => __('Assessment'),
                                        'heroicon-o-light-bulb' => __('Innovation'),
                                    ])
                                    ->searchable()
                                    ->default('heroicon-o-clipboard-document-list')
                                    ->columnSpan(1),

                                Forms\Components\Select::make('color')
                                    ->label(__('Color Theme'))
                                    ->options([
                                        'primary' => __('Primary'),
                                        'success' => __('Success'),
                                        'info' => __('Info'),
                                        'warning' => __('Warning'),
                                        'danger' => __('Danger'),
                                        'secondary' => __('Secondary'),
                                        'indigo' => __('Indigo'),
                                        'purple' => __('Purple'),
                                        'pink' => __('Pink'),
                                        'gray' => __('Gray'),
                                    ])
                                    ->default('primary')
                                    ->columnSpan(1),

                                // Forms\Components\TextInput::make('order')
                                //     ->label(__('Order'))
                                //     ->numeric()
                                //     ->default(fn() => (SurveyCategory::max('order') ?? 0) + 1)
                                //     ->helperText(__('Lower numbers appear first'))
                                //     ->columnSpan(1),
                            ]),

                        Forms\Components\Toggle::make('is_active')
                            ->label(__('Active'))
                            ->helperText(__('Only active categories can be used for new surveys'))
                            ->default(true),
                    ])
                    ->collapsible()
                    ->persistCollapsed()
                    ->compact(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Name'))
                    ->formatStateUsing(fn ($record) => $record->getTranslation('name', app()->getLocale()))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('slug')
                    ->label(__('Slug'))
                    ->searchable()
                    ->copyable()
                    ->copyMessage(__('Slug copied'))
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('description')
                    ->label(__('Description'))
                    ->formatStateUsing(fn ($record) => $record->getTranslation('description', app()->getLocale()))
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }

                        return $state;
                    })
                    ->toggleable(),

                Tables\Columns\IconColumn::make('icon')
                    ->label(__('Icon'))
                    ->icon(fn (string $state): string => $state)
                    ->color(fn ($record): string => $record->color)
                    ->size('lg'),

                Tables\Columns\TextColumn::make('color')
                    ->label(__('Color'))
                    ->badge()
                    ->color(fn (string $state): string => $state),

                Tables\Columns\TextColumn::make('surveys_count')
                    ->label(__('Surveys'))
                    ->counts('surveys')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                Tables\Columns\TextColumn::make('order')
                    ->label(__('Order'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('Active'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('Updated At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('is_active')
                    ->label(__('Status'))
                    ->options([
                        '1' => __('Active'),
                        '0' => __('Inactive'),
                    ])
                    ->placeholder(__('All Categories')),

                Tables\Filters\SelectFilter::make('color')
                    ->label(__('Color Theme'))
                    ->options([
                        'primary' => __('Primary'),
                        'success' => __('Success'),
                        'info' => __('Info'),
                        'warning' => __('Warning'),
                        'danger' => __('Danger'),
                        'secondary' => __('Secondary'),
                        'indigo' => __('Indigo'),
                        'purple' => __('Purple'),
                        'pink' => __('Pink'),
                        'gray' => __('Gray'),
                    ])
                    ->placeholder(__('All Colors')),
            ])
            ->recordActions([
                EditAction::make()
                    ->color('warning')
                    ->iconButton(),
                DeleteAction::make()
                    ->color('danger')
                    ->iconButton()
                    ->requiresConfirmation()
                    ->modalDescription(__('Are you sure you want to delete this category? This action cannot be undone.'))
                    ->before(function (SurveyCategory $record) {
                        if ($record->surveys()->count() > 0) {
                            throw new \Exception(__('Cannot delete category with existing surveys. Please reassign or delete the surveys first.'));
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalDescription(__('Are you sure you want to delete these categories? This action cannot be undone.'))
                        ->before(function ($records) {
                            foreach ($records as $record) {
                                if ($record->surveys()->count() > 0) {
                                    throw new \Exception(__('Cannot delete categories with existing surveys. Please reassign or delete the surveys first.'));
                                }
                            }
                        }),
                ])
                    ->label(__('Actions'))
                    ->color('primary')
                    ->icon('heroicon-o-ellipsis-horizontal'),
            ])
            ->defaultSort('order', 'asc')
            ->reorderable('order');
    }

    public static function getRelations(): array
    {
        return [
            // TODO: Add SurveysRelationManager when needed
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSurveyCategories::route('/'),
            'create' => Pages\CreateSurveyCategory::route('/create'),
            'edit' => Pages\EditSurveyCategory::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount(['surveys']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'slug', 'description'];
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            __('Slug') => $record->slug,
            __('Status') => $record->is_active ? __('Active') : __('Inactive'),
            __('Surveys') => $record->surveys_count ?? $record->surveys()->count(),
        ];
    }

    public static function canDeleteAny(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        return $user?->can('delete_survey_categories') ?? false;
    }
}
