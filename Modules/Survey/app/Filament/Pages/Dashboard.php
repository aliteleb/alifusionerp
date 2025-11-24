<?php

namespace Modules\Survey\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Modules\Survey\Filament\Widgets\CustomerEngagementStatsWidget;
use Modules\Survey\Filament\Widgets\MonthlyResponsesChartWidget;
use Modules\Survey\Filament\Widgets\QuickStatsWidget;
use Modules\Survey\Filament\Widgets\RecentResponsesTableWidget;
use Modules\Survey\Filament\Widgets\SafeSurveyStatsWidget;
use Modules\Survey\Filament\Widgets\SurveyCategoriesChartWidget;
use Modules\Survey\Filament\Widgets\SurveyResponsesChartWidget;

class Dashboard extends BaseDashboard
{
    public function getTitle(): string
    {
        return __('Survey Dashboard');
    }

    public function getWidgets(): array
    {
        return [
            SafeSurveyStatsWidget::class,
            CustomerEngagementStatsWidget::class,
            QuickStatsWidget::class,
            SurveyResponsesChartWidget::class,
            SurveyCategoriesChartWidget::class,
            MonthlyResponsesChartWidget::class,
            RecentResponsesTableWidget::class,
        ];
    }

    public function getWidgetData(): array
    {
        return [
            // TODO: Add widget configurations when widgets are created
        ];
    }

    public function getColumns(): array|int
    {
        return [
            'default' => 1,
            'sm' => 1,
            'md' => 2,
            'lg' => 3,
            'xl' => 3,
            '2xl' => 3,
        ];
    }
}
