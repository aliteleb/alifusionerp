<?php

namespace Modules\Core\Filament\Resources\Currencies;

use Modules\Core\Filament\Resources\Currencies\Pages\CreateCurrency;
use Modules\Core\Filament\Resources\Currencies\Pages\EditCurrency;
use Modules\Core\Filament\Resources\Currencies\Pages\ListCurrencies;
use Modules\Core\Filament\Resources\Currencies\Schemas\CurrencyForm;
use Modules\Core\Filament\Resources\Currencies\Tables\CurrenciesTable;
use Modules\Core\Entities\Currency;
use Modules\Core\Entities\User;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class CurrencyResource extends Resource
{
    protected static ?string $model = Currency::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-currency-dollar';

    public static function getNavigationGroup(): ?string
    {
        return __('System');
    }

    public static function getNavigationParentItem(): ?string
    {
        return __('Settings');
    }

    public static function getModelLabel(): string
    {
        return __('Currency');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Currencies');
    }

    public static function getNavigationLabel(): string
    {
        return __('Currencies');
    }

    public static function form(Schema $schema): Schema
    {
        return CurrencyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CurrenciesTable::configure($table);
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
            'index' => ListCurrencies::route('/'),
            // 'create' => CreateCurrency::route('/create'),
            // 'edit' => EditCurrency::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        /** @var User $user */
        $user = Filament::getCurrentOrDefaultPanel()->auth()->user();

        return $user->can('access_currencies');
    }

    public static function canView($record): bool
    {
        /** @var User $user */
        $user = Filament::getCurrentOrDefaultPanel()->auth()->user();

        return $user->can('view_currencies');
    }

    public static function canCreate(): bool
    {
        /** @var User $user */
        $user = Filament::getCurrentOrDefaultPanel()->auth()->user();

        return $user->can('create_currencies');
    }

    public static function canEdit($record): bool
    {
        /** @var User $user */
        $user = Filament::getCurrentOrDefaultPanel()->auth()->user();

        return $user->can('edit_currencies');
    }

    public static function canDelete($record): bool
    {
        /** @var User $user */
        $user = Filament::getCurrentOrDefaultPanel()->auth()->user();

        return $user->can('delete_currencies');
    }
}


