<?php

namespace Modules\Survey\Filament\Resources\SurveyCategoryResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Modules\Survey\Filament\Resources\SurveyCategoryResource;

class ListSurveyCategories extends ListRecords
{
    protected static string $resource = SurveyCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('All Categories'))
                ->icon('heroicon-o-squares-2x2')
                ->badge($this->getModel()::count()),

            'active' => Tab::make(__('Active'))
                ->icon('heroicon-o-check-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->badge($this->getModel()::where('is_active', true)->count()),

            'inactive' => Tab::make(__('Inactive'))
                ->icon('heroicon-o-x-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', false))
                ->badge($this->getModel()::where('is_active', false)->count()),

            'with_surveys' => Tab::make(__('With Surveys'))
                ->icon('heroicon-o-document-text')
                ->modifyQueryUsing(fn (Builder $query) => $query->has('surveys'))
                ->badge($this->getModel()::has('surveys')->count()),

            'empty' => Tab::make(__('Empty Categories'))
                ->icon('heroicon-o-folder')
                ->modifyQueryUsing(fn (Builder $query) => $query->doesntHave('surveys'))
                ->badge($this->getModel()::doesntHave('surveys')->count()),

            'trashed' => Tab::make(__('Trashed'))
                ->icon('heroicon-o-trash')
                ->modifyQueryUsing(fn (Builder $query) => $query->onlyTrashed())
                ->badge($this->getModel()::onlyTrashed()->count()),
        ];
    }
}
