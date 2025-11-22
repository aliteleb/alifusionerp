<?php

namespace Modules\Core\Filament\Resources\Currencies\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class CurrenciesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('country.name')
                    ->label(__('Country'))
                    ->sortable(query: function ($query, string $direction): void {
                        $query->join('countries', 'currencies.country_id', '=', 'countries.id')
                            ->orderByRaw("countries.name->>'en' ".strtoupper($direction))
                            ->select('currencies.*');
                    })
                    ->toggleable(),
                TextColumn::make('title')
                    ->label(__('Title'))
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('symbol')
                    ->label(__('Symbol'))
                    ->toggleable(),
                ToggleColumn::make('is_active')
                    ->label(__('Active'))
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('Updated At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()->iconButton(),
                DeleteAction::make()->iconButton(),
                ForceDeleteAction::make()->iconButton(),
                RestoreAction::make()->iconButton(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}


