<?php

namespace Modules\Survey\Filament\Resources;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Gender;
use App\Models\User;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Modules\Survey\Filament\Resources\CustomerResource\Pages;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;
use Ysfkaya\FilamentPhoneInput\PhoneInputNumberType;
use Ysfkaya\FilamentPhoneInput\Tables\PhoneColumn;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static BackedEnum|string|null $navigationIcon = Heroicon::Users;

    public static function getNavigationGroup(): ?string
    {
        return __('Survey Management');
    }

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getPluralLabel(): string
    {
        return __('Customers');
    }

    public static function getLabel(): string
    {
        return __('Customer');
    }

    public static function getNavigationLabel(): string
    {
        return __('Customers');
    }

    public static function canAccess(): bool
    {
        /** @var User $user */
        $user = Filament::getCurrentPanel()->auth()->user();

        return $user ? $user->can('access_customers') : false;
    }

    public static function canViewAny(): bool
    {
        /** @var User $user */
        $user = Filament::getCurrentPanel()->auth()->user();

        return $user ? $user->can('view_customers') : false;
    }

    public static function canView($record): bool
    {
        /** @var User $user */
        $user = Filament::getCurrentPanel()->auth()->user();

        return $user ? $user->can('view_customers') : false;
    }

    public static function canCreate(): bool
    {
        /** @var User $user */
        $user = Filament::getCurrentPanel()->auth()->user();

        return $user ? $user->can('create_customers') : false;
    }

    public static function canEdit($record): bool
    {
        /** @var User $user */
        $user = Filament::getCurrentPanel()->auth()->user();

        return $user ? $user->can('edit_customers') : false;
    }

    public static function canDelete($record): bool
    {
        /** @var User $user */
        $user = Filament::getCurrentPanel()->auth()->user();

        return $user ? $user->can('delete_customers') : false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Section::make(__('Branch Selection'))
                    ->description(__('Select which branch this customer belongs to'))
                    ->icon('heroicon-o-building-storefront')
                    ->schema([
                        Forms\Components\Select::make('branch_id')
                            ->label(__('Target Branch'))
                            ->options(Branch::pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->prefixIcon('heroicon-o-map-pin')
                            ->helperText(__('As an HQ user, you can select any branch from your facilities.'))
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->persistCollapsed()
                    ->compact(),

                Forms\Components\Section::make(__('Customer Information'))
                    ->description(__('Add a new customer for survey feedback'))
                    ->icon('heroicon-o-user-plus')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label(__('Name'))
                                    ->required()
                                    ->maxLength(100)
                                    ->placeholder(__('Enter customer name'))
                                    ->prefixIcon('heroicon-o-user')
                                    ->columnSpan(1),

                                PhoneInput::make('phone')
                                    ->label(__('Phone'))
                                    ->required()
                                    ->placeholder(__('Enter phone number'))
                                    ->helperText(__('Enter phone number with country code'))
                                    ->prefixIcon('heroicon-o-phone')
                                    ->unique(Customer::class, 'phone', ignoreRecord: true)
                                    ->columnSpan(1)
                                    ->defaultCountry('IQ') // Default to Iraq
                                    ->displayNumberFormat(PhoneInputNumberType::NATIONAL)
                                    ->inputNumberFormat(PhoneInputNumberType::INTERNATIONAL),

                                Forms\Components\DatePicker::make('birthday')
                                    ->label(__('Birthday'))
                                    ->maxDate(now())
                                    ->placeholder(__('Select birthday'))
                                    ->helperText(__("Customer's date of birth (optional)"))
                                    ->prefixIcon('heroicon-o-cake')
                                    ->displayFormat('d/m/Y')
                                    ->columnSpan(1),

                                Forms\Components\Select::make('gender_id')
                                    ->label(__('Gender'))
                                    ->options(Gender::pluck('name', 'id'))
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->placeholder(__('Select gender'))
                                    ->helperText(__('Customer gender is required'))
                                    ->prefixIcon('heroicon-o-identification')
                                    ->columnSpan(1),

                                Forms\Components\Textarea::make('address')
                                    ->label(__('Address'))
                                    ->rows(3)
                                    ->placeholder(__('Enter full address'))
                                    ->helperText(__("Customer's address (optional)"))
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('email')
                                    ->label(__('Email'))
                                    ->email()
                                    ->maxLength(150)
                                    ->placeholder('customer@example.com')
                                    ->helperText(__("Customer's email address (optional)"))
                                    ->prefixIcon('heroicon-o-envelope')
                                    ->unique(Customer::class, 'email', ignoreRecord: true)
                                    ->columnSpan(1),

                                Forms\Components\DateTimePicker::make('visit_time')
                                    ->label(__('Visit time'))
                                    ->default(now())
                                    ->required()
                                    ->helperText(__('Visit time will default to current date and time'))
                                    ->prefixIcon('heroicon-o-clock')
                                    ->displayFormat('d/m/Y H:i')
                                    ->columnSpan(2),
                            ]),
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
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-user')
                    ->iconColor('primary')
                    ->weight('bold'),

                PhoneColumn::make('phone')
                    ->label(__('Phone'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage(__('Phone copied'))
                    ->displayFormat(PhoneInputNumberType::NATIONAL)
                    ->icon('heroicon-o-phone')
                    ->iconColor('success')
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('email')
                    ->label(__('Email'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage(__('Email copied'))
                    ->icon('heroicon-o-envelope')
                    ->iconColor('info')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('gender.name')
                    ->label(__('Gender'))
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Male', 'ذكر', 'نێر' => 'info',
                        'Female', 'أنثى', 'مێ' => 'warning',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'Male', 'ذكر', 'نێر' => 'heroicon-o-user',
                        'Female', 'أنثى', 'مێ' => 'heroicon-o-user',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->toggleable(),

                Tables\Columns\TextColumn::make('birthday')
                    ->label(__('Birthday'))
                    ->date()
                    ->sortable()
                    ->icon('heroicon-o-cake')
                    ->iconColor('warning')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('age')
                    ->label(__('Age'))
                    ->getStateUsing(fn (Customer $record): string => $record->age ? $record->age.' '.__('years') : '-')
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        // Database-agnostic age sorting
                        $databaseDriver = config('database.default');

                        if ($databaseDriver === 'pgsql') {
                            // PostgreSQL syntax
                            return $query->orderByRaw("CASE WHEN birthday IS NULL THEN 1 ELSE 0 END, EXTRACT(YEAR FROM AGE(birthday)) $direction");
                        } else {
                            // MySQL/MariaDB syntax
                            return $query->orderByRaw("CASE WHEN birthday IS NULL THEN 1 ELSE 0 END, TIMESTAMPDIFF(YEAR, birthday, CURDATE()) $direction");
                        }
                    })
                    ->badge()
                    ->color(function ($state): string {
                        if ($state === '-') {
                            return 'gray';
                        }
                        $age = (int) str_replace(' '.__('years'), '', $state);

                        return match (true) {
                            $age < 25 => 'success',
                            $age < 40 => 'warning',
                            $age < 60 => 'info',
                            default => 'danger'
                        };
                    })
                    ->icon('heroicon-o-calendar-days')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('branch.name')
                    ->label(__('Branch'))
                    ->sortable(query: fn ($query, $direction) => $query->orderByJsonRelation('branch.name', $direction))
                    ->searchable()
                    ->badge()
                    ->color('primary')
                    ->icon('heroicon-o-building-storefront'),

                Tables\Columns\TextColumn::make('visit_time')
                    ->label(__('Visit Time'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since()
                    ->icon('heroicon-o-clock')
                    ->iconColor('info')
                    ->color(function ($record): string {
                        $days = now()->diffInDays($record->visit_time);

                        return match (true) {
                            $days <= 1 => 'success',
                            $days <= 7 => 'warning',
                            $days <= 30 => 'info',
                            default => 'danger'
                        };
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('gender_id')
                    ->label(__('Gender'))
                    ->options(Gender::pluck('name', 'id'))
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('branch_id')
                    ->label(__('Branch'))
                    ->options(Branch::pluck('name', 'id'))
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('recent_visits')
                    ->label(__('Recent Visits (30 days)'))
                    ->query(fn (Builder $query): Builder => $query->recentVisits(30)),

                Tables\Filters\Filter::make('birthday_this_month')
                    ->label(__('Birthday This Month'))
                    ->query(fn (Builder $query): Builder => $query->birthdayInMonth(now()->month)),
            ])
            ->recordActions([
                EditAction::make()
                    ->color('warning')
                    ->iconButton(),
                DeleteAction::make()
                    ->color('danger')
                    ->iconButton(),
                ForceDeleteAction::make()
                    ->color('danger')
                    ->icon('heroicon-o-trash')
                    ->iconButton()
                    ->visible(fn ($record) => $record->trashed()),
                RestoreAction::make()
                    ->color('success')
                    ->icon('heroicon-o-arrow-path')
                    ->iconButton(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->color('danger')
                        ->icon('heroicon-o-trash'),
                    ForceDeleteBulkAction::make()
                        ->color('danger')
                        ->icon('heroicon-o-x-circle'),
                    RestoreBulkAction::make()
                        ->color('success')
                        ->icon('heroicon-o-arrow-path'),
                ])
                    ->label(__('Actions'))
                    ->color('primary')
                    ->icon('heroicon-o-ellipsis-horizontal'),
            ])
            ->defaultSort('visit_time', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            // TODO: Add survey responses relation when survey responses table is created
            // RelationManagers\SurveyResponsesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            // 'view' => Pages\ViewCustomer::route('/{record}'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['branch', 'gender']);
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()
            ->with(['branch', 'gender']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'phone', 'email', 'branch.name'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            __('Phone') => $record->phone,
            __('Branch') => $record->branch?->name,
            __('Visit Time') => $record->visit_time?->diffForHumans(),
        ];
    }
}
