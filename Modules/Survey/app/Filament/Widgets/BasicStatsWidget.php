<?php

namespace Modules\Survey\Filament\Widgets;

use App\Models\Customer;
use App\Models\Survey;
use App\Models\SurveyResponse;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BasicStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        try {
            // Get actual system overview stats
            $totalSurveys = Survey::count();
            $totalResponses = SurveyResponse::count();
            $totalCustomers = Customer::count();

            return [
                Stat::make(__('System Overview'), $totalSurveys.' / '.$totalResponses.' / '.$totalCustomers)
                    ->description(__('Surveys / Responses / Customers'))
                    ->descriptionIcon('heroicon-m-chart-bar-square')
                    ->color('primary'),
            ];
        } catch (\Exception $e) {
            return [
                Stat::make(__('System Status'), 'Error')
                    ->description(__('Unable to load system data'))
                    ->descriptionIcon('heroicon-m-exclamation-triangle')
                    ->color('danger'),
            ];
        }
    }
}
