<?php

namespace Modules\Core\Filament\Resources\Branches\Tables;

use App\Filament\Exports\BranchExporter;
use Modules\Core\Entities\Branch;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportBulkAction;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BranchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->wrap(),

                IconColumn::make('is_active')
                    ->label(__('Status'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->tooltip(fn ($state) => $state ? __('Active') : __('Inactive'))
                    ->sortable()
                    ->toggleable(),

                IconColumn::make('is_hq')
                    ->label(__('HQ'))
                    ->boolean()
                    ->trueIcon('heroicon-o-building-office-2')
                    ->falseIcon('heroicon-o-building-storefront')
                    ->trueColor('warning')
                    ->falseColor('gray')
                    ->tooltip(fn ($state) => $state ? __('Headquarters') : __('Branch'))
                    ->sortable()
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
            ->filters([])
            ->recordActions([
                EditAction::make()
                    ->iconButton()
                    ->color('warning'),
                DeleteAction::make()
                    ->iconButton()
                    ->color('danger'),
                ForceDeleteAction::make()
                    ->iconButton()
                    ->color('danger'),
                RestoreAction::make()
                    ->iconButton()
                    ->color('success'),

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ExportBulkAction::make()
                        ->label(__('Export Selected'))
                        ->icon(Heroicon::ArrowDownTray)
                        ->color('primary')
                        ->outlined()
                        ->exporter(BranchExporter::class)
                        ->fileName(fn () => 'selected-branches-'.now()->format('Y-m-d-H-i-s'))
                        ->formats([
                            ExportFormat::Xlsx,
                            ExportFormat::Csv,
                        ])
                        ->columnMappingColumns(3),

                    BulkAction::make('activate')
                        ->label(__('Activate Selected'))
                        ->icon(Heroicon::PlayCircle)
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading(__('Activate Branches'))
                        ->modalDescription(__('Are you sure you want to activate the selected branches?'))
                        ->action(function ($records) {
                            $count = $records->count();
                            Branch::whereIn('id', $records->pluck('id'))->update(['is_active' => true]);
                            Notification::make()
                                ->title(__(':count branches activated', ['count' => $count]))
                                ->success()
                                ->send();
                        }),

                    BulkAction::make('deactivate')
                        ->label(__('Deactivate Selected'))
                        ->icon(Heroicon::PauseCircle)
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading(__('Deactivate Branches'))
                        ->modalDescription(__('Are you sure you want to deactivate the selected branches?'))
                        ->action(function ($records) {
                            $count = $records->count();
                            Branch::whereIn('id', $records->pluck('id'))->update(['is_active' => false]);
                            Notification::make()
                                ->title(__(':count branches deactivated', ['count' => $count]))
                                ->warning()
                                ->send();
                        }),

                    DeleteBulkAction::make()
                        ->label(__('Delete Selected'))
                        ->color('danger'),

                    ForceDeleteBulkAction::make()
                        ->label(__('Force Delete Selected'))
                        ->color('danger'),

                    RestoreBulkAction::make()
                        ->label(__('Restore Selected'))
                        ->color('success'),
                ]),
            ]);
    }
}


