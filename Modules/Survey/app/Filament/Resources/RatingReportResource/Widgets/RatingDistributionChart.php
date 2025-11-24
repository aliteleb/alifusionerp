<?php

namespace Modules\Survey\Filament\Resources\RatingReportResource\Widgets;

use App\Models\SurveyResponse;
use Filament\Widgets\ChartWidget;

class RatingDistributionChart extends ChartWidget
{
    public function getHeading(): ?string
    {
        return __('Rating Distribution');
    }

    protected function getMaxHeight(): ?string
    {
        return '300px';
    }

    protected function getData(): array
    {
        $ratingCounts = [
            __('1 Star') => SurveyResponse::where('average_rating', '>=', 1.0)->where('average_rating', '<', 1.5)->count(),
            __('2 Stars') => SurveyResponse::where('average_rating', '>=', 1.5)->where('average_rating', '<', 2.5)->count(),
            __('3 Stars') => SurveyResponse::where('average_rating', '>=', 2.5)->where('average_rating', '<', 3.5)->count(),
            __('4 Stars') => SurveyResponse::where('average_rating', '>=', 3.5)->where('average_rating', '<', 4.5)->count(),
            __('5 Stars') => SurveyResponse::where('average_rating', '>=', 4.5)->where('average_rating', '<=', 5.0)->count(),
        ];

        return [
            'datasets' => [
                [
                    'label' => __('Rating Distribution'),
                    'data' => array_values($ratingCounts),
                    'backgroundColor' => [
                        'rgb(239, 68, 68)',   // red for 1 star
                        'rgb(245, 158, 11)',  // orange for 2 stars
                        'rgb(107, 114, 128)', // gray for 3 stars
                        'rgb(59, 130, 246)',  // blue for 4 stars
                        'rgb(34, 197, 94)',   // green for 5 stars
                    ],
                ],
            ],
            'labels' => array_keys($ratingCounts),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
