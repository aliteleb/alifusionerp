<?php

namespace Modules\Core\Filament\Resources\Departments\Tables;

use App\Filament\Exports\DepartmentExporter;
use Modules\Core\Entities\Department;
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
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DepartmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('code')
                    ->label(__('Code'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('branch.name')
                    ->label(__('Branch'))
                    ->searchable()
                    ->sortable(query: fn ($query, $direction) => $query->orderByJsonRelation('branch.name', $direction))
                    ->badge()
                    ->formatStateUsing(function ($record) {
                        return $record->branch?->getTranslation('name', app()->getLocale());
                    })
                    ->color('success')
                    ->toggleable(),

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

                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('branch')
                    ->label(__('Branch'))
                    ->relationship('branch', 'name', fn ($query) => $query->orderBy('id'))
                    ->searchable()
                    ->multiple()
                    ->preload()
                    ->placeholder(__('All Branches')),
            ])
            ->filtersFormColumns(1)
            ->filtersFormWidth('full')
            ->persistFiltersInSession()
            ->deferFilters(false)
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
                        ->exporter(DepartmentExporter::class)
                        ->fileName(fn () => 'selected-departments-'.now()->format('Y-m-d-H-i-s'))
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
                        ->modalHeading(__('Activate Departments'))
                        ->modalDescription(__('Are you sure you want to activate the selected departments?'))
                        ->action(function ($records) {
                            $count = $records->count();
                            Department::whereIn('id', $records->pluck('id'))->update(['is_active' => true]);
                            Notification::make()
                                ->title(__(':count departments activated', ['count' => $count]))
                                ->success()
                                ->send();
                        }),

                    BulkAction::make('deactivate')
                        ->label(__('Deactivate Selected'))
                        ->icon(Heroicon::PauseCircle)
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading(__('Deactivate Departments'))
                        ->modalDescription(__('Are you sure you want to deactivate the selected departments?'))
                        ->action(function ($records) {
                            $count = $records->count();
                            Department::whereIn('id', $records->pluck('id'))->update(['is_active' => false]);
                            Notification::make()
                                ->title(__(':count departments deactivated', ['count' => $count]))
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


