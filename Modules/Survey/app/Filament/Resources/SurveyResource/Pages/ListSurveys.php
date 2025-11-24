<?php

namespace Modules\Survey\Filament\Resources\SurveyResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Modules\Survey\Filament\Resources\SurveyResource;

class ListSurveys extends ListRecords
{
    protected static string $resource = SurveyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('All Surveys'))
                ->icon('heroicon-o-clipboard-document-list')
                ->badge($this->getModel()::count()),

            'draft' => Tab::make(__('Draft'))
                ->icon('heroicon-o-document')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'draft'))
                ->badge($this->getModel()::where('status', 'draft')->count()),

            'active' => Tab::make(__('Active'))
                ->icon('heroicon-o-play')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'active'))
                ->badge($this->getModel()::where('status', 'active')->count()),

            'paused' => Tab::make(__('Paused'))
                ->icon('heroicon-o-pause')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'paused'))
                ->badge($this->getModel()::where('status', 'paused')->count()),

            'completed' => Tab::make(__('Completed'))
                ->icon('heroicon-o-check-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'completed'))
                ->badge($this->getModel()::where('status', 'completed')->count()),

            'archived' => Tab::make(__('Archived'))
                ->icon('heroicon-o-archive-box')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'archived'))
                ->badge($this->getModel()::where('status', 'archived')->count()),

            'trashed' => Tab::make(__('Trashed'))
                ->icon('heroicon-o-trash')
                ->modifyQueryUsing(fn (Builder $query) => $query->onlyTrashed())
                ->badge($this->getModel()::onlyTrashed()->count()),
        ];
    }
}
