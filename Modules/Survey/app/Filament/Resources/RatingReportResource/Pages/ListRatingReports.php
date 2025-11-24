<?php

namespace Modules\Survey\Filament\Resources\RatingReportResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Survey\Filament\Resources\RatingReportResource;

class ListRatingReports extends ListRecords
{
    protected static string $resource = RatingReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export_all_pdf')
                ->label(__('Export All (PDF)'))
                ->icon('heroicon-o-document-arrow-down')
                ->color('danger')
                ->url(function () {
                    return route('rating-report.all-pdf', request()->query());
                })
                ->openUrlInNewTab(),

            Actions\Action::make('export_all_excel')
                ->label(__('Export All (Excel)'))
                ->icon('heroicon-o-table-cells')
                ->color('success')
                ->url(function () {
                    return route('rating-report.all-excel', request()->query());
                })
                ->openUrlInNewTab(),

            Actions\Action::make('rating_analytics')
                ->label(__('Rating Analytics'))
                ->icon('heroicon-o-chart-bar')
                ->color('info')
                ->url(function () {
                    return route('filament.admin.resources.rating-reports.analytics');
                })
                ->openUrlInNewTab(),
        ];
    }
}
