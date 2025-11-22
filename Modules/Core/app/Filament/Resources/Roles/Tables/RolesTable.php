<?php

namespace Modules\Core\Filament\Resources\Roles\Tables;

use Modules\Core\Entities\Role;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RolesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->icon(Heroicon::Tag)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('permissions_count')
                    ->label('Permissions Count')
                    ->icon(Heroicon::ShieldCheck)
                    ->counts('permissions')
                    ->translateLabel()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label(__('Created at'))
                    ->icon(Heroicon::Clock)
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('Updated at'))
                    ->icon(Heroicon::PencilSquare)
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->iconButton()
                    ->color('warning'),
                Action::make('duplicate')
                    ->label(__('Duplicate'))
                    ->icon('heroicon-o-document-duplicate')
                    ->color('success')
                    ->schema([
                        TextInput::make('new_name')
                            ->label(__('New Role Name'))
                            ->required()
                            ->maxLength(255)
                            ->unique(Role::class, 'name'),
                    ])
                    ->action(function (Role $record, array $data) {
                        // Create the new role
                        $newRole = Role::create(['name' => $data['new_name'], 'guard_name' => $record->guard_name]);

                        // Get permissions from the original role
                        $permissions = $record->permissions()->pluck('name');

                        // Assign permissions to the new role
                        $newRole->syncPermissions($permissions);

                        Notification::make()
                            ->title(__('Role duplicated successfully'))
                            ->success()
                            ->send();

                        return redirect()->route('filament.admin.resources.roles.edit', $newRole->id);
                    }),
                DeleteAction::make()
                    ->iconButton()
                    ->color('danger')
                    ->before(function (Role $record) {
                        if ($record->name === 'admin') {
                            return false;
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label(__('Delete Selected'))
                        ->color('danger'),
                ]),
            ]);
    }
}


