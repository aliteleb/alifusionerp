<?php

namespace Modules\Master\Filament\Master\Resources;

use Modules\Master\Filament\Master\Resources\PermissionResource\Pages;
use Modules\Master\Filament\Master\Resources\PermissionResource\Pages\ListPermissions;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

class PermissionResource extends Resource
{
    protected static ?string $model = Permission::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-key';

    protected static ?int $navigationSort = 3;

    public static function getModelLabel(): string
    {
        return __('Permission');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Permissions');
    }

    public static function getNavigationLabel(): string
    {
        return __('Permissions Management');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('System');
    }
    // parent resource
    // public static function getParentResource(): ?string
    // {
    //     return RoleResource::class;
    // }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('Name'))
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->helperText(__('Permission name should start with `access_` followed by the resource name (e.g., access_banners)')),
                TextInput::make('guard_name')
                    ->label(__('Guard Name'))
                    ->default('web')
                    ->required()
                    ->maxLength(255),
                TextInput::make('display_name')
                    ->label(__('Display Name'))
                    ->helperText(__('A human-readable name for this permission (optional)'))
                    ->maxLength(255),
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('label')
                    ->label(__('Label'))
                    ->getStateUsing(function ($record) {
                        $value = Str::of($record->name)
                            ->replace('access_', '')
                            ->replace('_', ' ')
                            ->title();

                        return __($value->toString(), locale: app()->getLocale());
                    }),
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('Created at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->recordActions([
                EditAction::make()->label(__('Edit')),
                DeleteAction::make()->label(__('Delete')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label(__('Delete')),
                ]),
            ])
            ->defaultSort('id');
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
            'index' => ListPermissions::route('/'),
            // 'create' => Pages\CreatePermission::route('/create'),
            // 'edit' => Pages\EditPermission::route('/{record}/edit'),
        ];
    }

    // can access
    public static function canAccess(): bool
    {
        return false;
    }
}
