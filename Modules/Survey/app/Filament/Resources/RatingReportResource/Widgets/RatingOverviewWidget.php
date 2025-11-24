<?php

namespace Modules\Survey\Filament\Resources\RatingReportResource\Widgets;

use App\Models\SurveyResponse;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RatingOverviewWidget extends StatsOverviewWidget
{
    protected function getMaxHeight(): ?string
    {
        return '200px';
    }

    protected function getStats(): array
    {
        $totalRatings = SurveyResponse::whereNotNull('average_rating')->count();
        $avgRating = SurveyResponse::whereNotNull('average_rating')->avg('average_rating');
        $excellentRatings = SurveyResponse::where('average_rating', '>=', 4.5)->count();
        $poorRatings = SurveyResponse::where('average_rating', '<=', 2.0)->count();

        $excellentPercentage = $totalRatings > 0 ? round(($excellentRatings / $totalRatings) * 100, 1) : 0;
        $poorPercentage = $totalRatings > 0 ? round(($poorRatings / $totalRatings) * 100, 1) : 0;

        return [
            Stat::make(__('Total Ratings'), number_format($totalRatings))
                ->description(__('Survey responses with ratings'))
                ->descriptionIcon('heroicon-m-star')
                ->color('info'),

            Stat::make(__('Average Rating'), $avgRating ? number_format($avgRating, 2).' ⭐' : __('N/A'))
                ->description(__('Overall average rating'))
                ->descriptionIcon('heroicon-m-trophy')
                ->color($avgRating >= 4 ? 'success' : ($avgRating >= 3 ? 'warning' : 'danger')),

            Stat::make(__('Excellent Ratings'), $excellentPercentage.'%')
                ->description($excellentRatings.' '.__('ratings (4.5+ stars)'))
                ->descriptionIcon('heroicon-m-face-smile')
                ->color('success'),

            Stat::make(__('Poor Ratings'), $poorPercentage.'%')
                ->description($poorRatings.' '.__('ratings (2 stars or less)'))
                ->descriptionIcon('heroicon-m-face-frown')
                ->color('danger'),
        ];
    }
}
