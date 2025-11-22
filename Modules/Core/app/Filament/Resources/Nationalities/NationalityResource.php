<?php

namespace Modules\Core\Filament\Resources\Nationalities;

use Modules\Core\Filament\Resources\Nationalities\Pages\CreateNationality;
use Modules\Core\Filament\Resources\Nationalities\Pages\EditNationality;
use Modules\Core\Filament\Resources\Nationalities\Pages\ListNationalities;
use Modules\Core\Filament\Resources\Nationalities\Schemas\NationalityForm;
use Modules\Core\Filament\Resources\Nationalities\Tables\NationalitiesTable;
use Modules\Core\Entities\Nationality;
use Modules\Core\Entities\User;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class NationalityResource extends Resource
{
    protected static ?string $model = Nationality::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-flag';

    public static function getNavigationLabel(): string
    {
        return __('Nationalities');
    }

    public static function getModelLabel(): string
    {
        return __('Nationality');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Nationalities');
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
        return 12;
    }

    public static function form(Schema $schema): Schema
    {
        return NationalityForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NationalitiesTable::configure($table);
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
            'index' => ListNationalities::route('/'),
            // 'create' => CreateNationality::route('/create'),
            // 'edit' => EditNationality::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        /** @var User $user */
        $user = Filament::getCurrentOrDefaultPanel()->auth()->user();

        return $user ? $user->can('access_nationalities') : false;
    }

    public static function canView($record): bool
    {
        /** @var User $user */
        $user = Filament::getCurrentOrDefaultPanel()->auth()->user();

        return $user->can('view_nationalities');
    }

    public static function canCreate(): bool
    {
        /** @var User $user */
        $user = Filament::getCurrentOrDefaultPanel()->auth()->user();

        return $user->can('create_nationalities');
    }

    public static function canEdit($record): bool
    {
        /** @var User $user */
        $user = Filament::getCurrentOrDefaultPanel()->auth()->user();

        return $user->can('edit_nationalities');
    }

    public static function canDelete($record): bool
    {
        /** @var User $user */
        $user = Filament::getCurrentOrDefaultPanel()->auth()->user();

        return $user->can('delete_nationalities');
    }
}


