<?php

namespace Modules\Core\Filament\Resources\Genders\Pages;

use Modules\Core\Filament\Resources\Genders\GenderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use pxlrbt\FilamentExcel\Actions\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class ListGenders extends ListRecords
{
    protected static string $resource = GenderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            ExportAction::make()->exports([
                ExcelExport::make('table')->fromTable()
                    ->askForFilename()
                    ->askForWriterType(),
            ]),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('All'))
                ->icon('heroicon-o-user')
                ->modifyQueryUsing(fn (Builder $query) => $query->withTrashed())
                ->badge(static::getModel()::withTrashed()->count()),
            'active' => Tab::make(__('Active'))
                ->icon('heroicon-o-check-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->withoutTrashed()->where('is_active', true))
                ->badge(static::getModel()::withoutTrashed()->where('is_active', true)->count()),
            'trashed' => Tab::make(__('Trashed'))
                ->icon('heroicon-o-trash')
                ->modifyQueryUsing(fn (Builder $query) => $query->onlyTrashed())
                ->badge(static::getModel()::onlyTrashed()->count()),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'active';
    }
}


