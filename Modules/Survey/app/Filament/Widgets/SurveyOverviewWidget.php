<?php

namespace Modules\Survey\Filament\Widgets;

use App\Models\Survey;
use App\Models\SurveyResponse;
use App\Services\TenantDatabaseService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class SurveyOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // Connect to current tenant database
        $facility = Auth::user()?->facility ?? \App\Models\Facility::first();
        if ($facility) {
            TenantDatabaseService::switchToTenant($facility);
        }

        $totalSurveys = Survey::count();
        $activeSurveys = Survey::where('status', 'active')->count();
        $totalResponses = SurveyResponse::count();

        return [
            Stat::make(__('Total Surveys'), number_format($totalSurveys))
                ->description(__('All surveys in system'))
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('primary'),

            Stat::make(__('Active Surveys'), number_format($activeSurveys))
                ->description(__('Currently running'))
                ->descriptionIcon('heroicon-m-play')
                ->color('success'),

            Stat::make(__('Total Responses'), number_format($totalResponses))
                ->description(__('All time responses'))
                ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                ->color('info'),
        ];
    }
}
