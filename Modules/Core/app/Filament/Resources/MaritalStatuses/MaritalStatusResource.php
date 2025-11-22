<?php

namespace Modules\Core\Filament\Resources\MaritalStatuses;

use Modules\Core\Filament\Resources\MaritalStatuses\Pages\CreateMaritalStatus;
use Modules\Core\Filament\Resources\MaritalStatuses\Pages\EditMaritalStatus;
use Modules\Core\Filament\Resources\MaritalStatuses\Pages\ListMaritalStatuses;
use Modules\Core\Filament\Resources\MaritalStatuses\Schemas\MaritalStatusForm;
use Modules\Core\Filament\Resources\MaritalStatuses\Tables\MaritalStatusesTable;
use Modules\Core\Entities\MaritalStatus;
use Modules\Core\Entities\User;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class MaritalStatusResource extends Resource
{
    protected static ?string $model = MaritalStatus::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-heart';

    public static function getNavigationLabel(): string
    {
        return __('Marital Status');
    }

    public static function getModelLabel(): string
    {
        return __('Marital Status');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Marital Status');
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
        return 11;
    }

    public static function form(Schema $schema): Schema
    {
        return MaritalStatusForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MaritalStatusesTable::configure($table);
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
            'index' => ListMaritalStatuses::route('/'),
            // 'create' => CreateMaritalStatus::route('/create'),
            // 'edit' => EditMaritalStatus::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        /** @var User $user */
        $user = Filament::getCurrentOrDefaultPanel()->auth()->user();

        return $user ? $user->can('access_marital_statuses') : false;
    }

    public static function canView($record): bool
    {
        /** @var User $user */
        $user = Filament::getCurrentOrDefaultPanel()->auth()->user();

        return $user->can('view_marital_statuses');
    }

    public static function canCreate(): bool
    {
        /** @var User $user */
        $user = Filament::getCurrentOrDefaultPanel()->auth()->user();

        return $user->can('create_marital_statuses');
    }

    public static function canEdit($record): bool
    {
        /** @var User $user */
        $user = Filament::getCurrentOrDefaultPanel()->auth()->user();

        return $user->can('edit_marital_statuses');
    }

    public static function canDelete($record): bool
    {
        /** @var User $user */
        $user = Filament::getCurrentOrDefaultPanel()->auth()->user();

        return $user->can('delete_marital_statuses');
    }
}


