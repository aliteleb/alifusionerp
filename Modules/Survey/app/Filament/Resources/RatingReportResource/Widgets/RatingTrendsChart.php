<?php

namespace Modules\Survey\Filament\Resources\RatingReportResource\Widgets;

use App\Models\SurveyResponse;
use Filament\Widgets\ChartWidget;

class RatingTrendsChart extends ChartWidget
{
    public function getHeading(): ?string
    {
        return __('Rating Trends (Last 30 Days)');
    }

    protected function getMaxHeight(): ?string
    {
        return '300px';
    }

    protected function getData(): array
    {
        $last30Days = collect(range(29, 0))->map(function ($daysAgo) {
            $date = now()->subDays($daysAgo);
            $avgRating = SurveyResponse::whereDate('started_at', $date)
                ->whereNotNull('average_rating')
                ->avg('average_rating');

            return [
                'date' => $date->format('M d'),
                'rating' => $avgRating ? round($avgRating, 2) : 0,
            ];
        });

        return [
            'datasets' => [
                [
                    'label' => __('Average Daily Rating'),
                    'data' => $last30Days->pluck('rating')->toArray(),
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $last30Days->pluck('date')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
