<?php

namespace Modules\Core\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Full Name'))
                    ->icon(Heroicon::User)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('branch.name')
                    ->label(__('Branch'))
                    ->icon(Heroicon::BuildingOffice2)
                    ->badge()
                    ->color('info')
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query->orderByJsonRelation('branch.name', $direction))
                    ->toggleable(),
                TextColumn::make('employment_id')
                    ->label(__('Employment ID'))
                    ->icon(Heroicon::Identification)
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('email')
                    ->label(__('Email'))
                    ->icon(Heroicon::Envelope)
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('department.name')
                    ->label(__('Department'))
                    ->icon(Heroicon::BuildingOffice2)
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query->orderByJsonRelation('department.name', $direction))
                    ->toggleable(),
                TextColumn::make('roles.name')
                    ->label(__('Roles'))
                    ->icon(Heroicon::ShieldCheck)
                    ->badge()
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label(__('Created at'))
                    ->icon(Heroicon::Plus)
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('Updated at'))
                    ->icon(Heroicon::Pencil)
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('branch_id')
                    ->label(__('Branch'))
                    ->relationship('branch', 'name', fn ($query) => $query->active()->orderBy('name->'.app()->getLocale()))
                    ->searchable()
                    ->preload()
                    ->multiple(),

                SelectFilter::make('department_id')
                    ->label(__('Department'))
                    ->relationship('department', 'name', fn ($query) => $query->active()->orderBy('name->'.app()->getLocale()))
                    ->searchable()
                    ->preload()
                    ->multiple(),

            ])
            ->filtersFormColumns(4)
            ->filtersFormWidth('full')
            ->deferFilters(false)
            ->persistFiltersInSession()
            ->recordActions([
                EditAction::make()
                    ->label(__('Edit'))
                    ->icon(Heroicon::Pencil)
                    ->iconButton()
                    ->color('warning'),
                DeleteAction::make()
                    ->label(__('Delete'))
                    ->icon(Heroicon::Trash)
                    ->iconButton()
                    ->color('danger'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label(__('Delete Selected'))
                        ->color('danger'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }
}


