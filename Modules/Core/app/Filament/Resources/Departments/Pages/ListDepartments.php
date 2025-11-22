<?php

namespace Modules\Core\Filament\Resources\Departments\Pages;

use App\Filament\Exports\DepartmentExporter;
use Modules\Core\Filament\Resources\Departments\DepartmentResource;
use Modules\Core\Traits\HasActiveStatusTabs;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListDepartments extends ListRecords
{
    use HasActiveStatusTabs;

    protected static string $resource = DepartmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),

            ExportAction::make()
                ->label(__('Export Departments'))
                ->icon(Heroicon::ArrowDownTray)
                ->color('primary')
                ->outlined()
                ->exporter(DepartmentExporter::class)
                ->fileName(fn () => strtolower('Departments').'-'.now()->format('Y-m-d-H-i-s'))
                ->formats([
                    ExportFormat::Xlsx,
                    ExportFormat::Csv,
                ])
                ->columnMappingColumns(3), // 3-column layout for column selection
        ];
    }
}


