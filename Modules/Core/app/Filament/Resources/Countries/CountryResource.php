<?php

namespace Modules\Core\Filament\Resources\Countries;

use Modules\Core\Filament\Resources\Countries\Pages\CreateCountry;
use Modules\Core\Filament\Resources\Countries\Pages\EditCountry;
use Modules\Core\Filament\Resources\Countries\Pages\ListCountries;
use Modules\Core\Filament\Resources\Countries\Schemas\CountryForm;
use Modules\Core\Filament\Resources\Countries\Tables\CountriesTable;
use Modules\Core\Entities\Country;
use Modules\Core\Entities\User;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CountryResource extends Resource
{
    protected static ?string $model = Country::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    public static function getNavigationLabel(): string
    {
        return __('Countries');
    }

    public static function getModelLabel(): string
    {
        return __('Country');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Countries');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('System');
    }

    public static function getNavigationParentItem(): ?string
    {
        return __('Settings');
    }

    public static function getNavigationSort(): ?int
    {
        return 13;
    }

    public static function form(Schema $schema): Schema
    {
        return CountryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CountriesTable::configure($table);
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
            'index' => ListCountries::route('/'),
            // 'create' => CreateCountry::route('/create'),
            // 'edit' => EditCountry::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function canAccess(): bool
    {
        /** @var User $user */
        $user = Filament::getCurrentOrDefaultPanel()->auth()->user();

        return $user ? $user->can('access_countries') : false;
    }

    public static function canView($record): bool
    {
        /** @var User $user */
        $user = Filament::getCurrentOrDefaultPanel()->auth()->user();

        return $user->can('view_countries');
    }

    public static function canCreate(): bool
    {
        /** @var User $user */
        $user = Filament::getCurrentOrDefaultPanel()->auth()->user();

        return $user->can('create_countries');
    }

    public static function canEdit($record): bool
    {
        /** @var User $user */
        $user = Filament::getCurrentOrDefaultPanel()->auth()->user();

        return $user->can('edit_countries');
    }

    public static function canDelete($record): bool
    {
        /** @var User $user */
        $user = Filament::getCurrentOrDefaultPanel()->auth()->user();

        return $user->can('delete_countries');
    }
}


