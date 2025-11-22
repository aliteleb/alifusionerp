<?php

namespace Modules\Core\Filament\Resources\Branches\Pages;

use App\Filament\Exports\BranchExporter;
use Modules\Core\Filament\Resources\Branches\BranchResource;
use Modules\Core\Traits\HasActiveStatusTabs;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListBranches extends ListRecords
{
    use HasActiveStatusTabs;

    protected static string $resource = BranchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label(__('New Branch'))
                ->icon(Heroicon::Plus),

            ExportAction::make()
                ->label(__('Export Branches'))
                ->icon(Heroicon::ArrowDownTray)
                ->color('primary')
                ->outlined()
                ->exporter(BranchExporter::class)
                ->fileName(fn () => 'branches-'.now()->format('Y-m-d-H-i-s'))
                ->formats([
                    ExportFormat::Xlsx,
                    ExportFormat::Csv,
                ])
                ->columnMappingColumns(3), // 3-column layout for column selection
        ];
    }
}


