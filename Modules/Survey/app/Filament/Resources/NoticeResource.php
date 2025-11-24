<?php

namespace Modules\Survey\Filament\Resources;

use App\Models\Notice;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Modules\Survey\Filament\Resources\NoticeResource\Pages;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class NoticeResource extends Resource
{
    protected static ?string $model = Notice::class;

    protected static BackedEnum|string|null $navigationIcon = Heroicon::Bell;

    public static function getNavigationLabel(): string
    {
        return __('Notices');
    }

    public static function getModelLabel(): string
    {
        return __('Notice');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Notices');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Administration');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([

                Translate::make()
                    ->locales(appLocales())
                    ->schema([
                        TextInput::make('type')
                            ->label(__('Notice Type'))
                            ->required(),
                        TextInput::make('description')
                            ->label(__('Description'))
                            ->required(),
                    ]),

                Forms\Components\Section::make(__('Notice Period'))
                    ->schema([
                        DatePicker::make('start_date')
                            ->label(__('Start Date'))
                            ->default(now())
                            ->required(),
                        DatePicker::make('end_date')
                            ->label(__('End Date'))
                            ->required()
                            ->after('start_date'),
                    ])
                    ->columns(2),

                Select::make('by')
                    ->label(__('Notice By'))
                    ->relationship(
                        name: 'author',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query) => $query->orderBy('name')
                    )
                    ->searchable()
                    ->preload()
                    ->required(),
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->columns([
                TextColumn::make('type')
                    ->label(__('Notice Type'))
                    ->searchable()
                    ->wrap(),
                TextColumn::make('description')
                    ->label(__('Description'))
                    ->limit(50)
                    ->searchable()
                    ->wrap()
                    ->toggleable(),
                TextColumn::make('start_date')
                    ->label(__('Start Date'))
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('end_date')
                    ->label(__('End Date'))
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('author.name')
                    ->label(__('Notice By'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('active_notices')
                    ->label(__('Active Notices'))
                    ->query(fn (Builder $query): Builder => $query
                        ->where('start_date', '<=', now())
                        ->where('end_date', '>=', now())
                    ),
                Tables\Filters\Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('from_date')
                            ->label(__('From Date'))
                            ->columnSpan(1),
                        Forms\Components\DatePicker::make('to_date')
                            ->label(__('To Date'))
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from_date'],
                                fn (Builder $query, $date): Builder => $query->whereDate('start_date', '>=', $date),
                            )
                            ->when(
                                $data['to_date'],
                                fn (Builder $query, $date): Builder => $query->whereDate('end_date', '<=', $date),
                            );
                    }),
            ])
            ->filtersLayout(FiltersLayout::AboveContent)
            ->persistFiltersInSession()
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                ForceDeleteAction::make()
                    ->visible(fn ($record) => $record->trashed()),
                RestoreAction::make()
                    ->visible(fn ($record) => $record->trashed()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListNotices::route('/'),
            // 'create' => Pages\CreateNotice::route('/create'),
            // 'edit' => Pages\EditNotice::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        /** @var \App\Models\User $user */
        $user = \Filament\Facades\Filament::getCurrentPanel()->auth()->user();

        return $user->can('access_notices');
    }

    public static function canView($record): bool
    {
        /** @var \App\Models\User $user */
        $user = \Filament\Facades\Filament::getCurrentPanel()->auth()->user();

        return $user->can('view_notices');
    }

    public static function canCreate(): bool
    {
        /** @var \App\Models\User $user */
        $user = \Filament\Facades\Filament::getCurrentPanel()->auth()->user();

        return $user->can('create_notices');
    }

    public static function canEdit($record): bool
    {
        /** @var \App\Models\User $user */
        $user = \Filament\Facades\Filament::getCurrentPanel()->auth()->user();

        return $user->can('edit_notices');
    }

    public static function canDelete($record): bool
    {
        /** @var \App\Models\User $user */
        $user = \Filament\Facades\Filament::getCurrentPanel()->auth()->user();

        return $user->can('delete_notices');
    }
}
