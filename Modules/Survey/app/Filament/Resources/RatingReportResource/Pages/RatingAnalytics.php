<?php

namespace Modules\Survey\Filament\Resources\RatingReportResource\Pages;

use Filament\Resources\Pages\Page;
use Modules\Survey\Filament\Resources\RatingReportResource;
use Modules\Survey\Filament\Resources\RatingReportResource\Widgets\BranchRatingComparisonChart;
use Modules\Survey\Filament\Resources\RatingReportResource\Widgets\RatingDistributionChart;
use Modules\Survey\Filament\Resources\RatingReportResource\Widgets\RatingOverviewWidget;
use Modules\Survey\Filament\Resources\RatingReportResource\Widgets\RatingTrendsChart;

class RatingAnalytics extends Page
{
    protected static string $resource = RatingReportResource::class;

    protected string $view = 'filament.resources.rating-report.analytics';

    protected static ?string $title = null;

    protected static ?string $navigationLabel = null;

    public static function getNavigationLabel(): string
    {
        return __('Analytics');
    }

    public function getTitle(): string
    {
        return __('Rating Analytics Dashboard');
    }

    protected function getHeaderWidgets(): array
    {
        return [
            RatingOverviewWidget::class,
            RatingDistributionChart::class,
            RatingTrendsChart::class,
            BranchRatingComparisonChart::class,
        ];
    }
}
