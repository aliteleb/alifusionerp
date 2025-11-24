<?php

namespace Modules\Survey\Filament\Widgets;

use App\Models\Survey;
use App\Models\SurveyResponse;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Log;

class SafeSurveyStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        try {
            // The TenantDatabaseMiddleware should have already switched to the correct tenant database
            // We don't need to manually switch here since we're already in the tenant context

            // Get real data from the database
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
        } catch (\Exception $e) {
            // Log the actual error for debugging
            Log::error('SafeSurveyStatsWidget error: '.$e->getMessage());

            // Return fallback data when there's an error
            return [
                Stat::make(__('Total Surveys'), '0')
                    ->description(__('All surveys in system'))
                    ->descriptionIcon('heroicon-m-clipboard-document-list')
                    ->color('primary'),

                Stat::make(__('Active Surveys'), '0')
                    ->description(__('Currently running'))
                    ->descriptionIcon('heroicon-m-play')
                    ->color('success'),

                Stat::make(__('Total Responses'), '0')
                    ->description(__('Error loading data - check logs'))
                    ->descriptionIcon('heroicon-m-exclamation-triangle')
                    ->color('danger'),
            ];
        }
    }
}
