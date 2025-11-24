<?php

namespace Modules\Survey\Filament\Resources\RatingReportResource\Widgets;

use App\Models\Branch;
use Filament\Widgets\ChartWidget;

class BranchRatingComparisonChart extends ChartWidget
{
    protected int|string|array $columnSpan = 2;

    public function getHeading(): ?string
    {
        return __('Branch Rating Comparison');
    }

    protected function getMaxHeight(): ?string
    {
        return '300px';
    }

    protected function getData(): array
    {
        $branchRatings = Branch::withCount(['surveyResponses as total_ratings' => function ($query) {
            $query->whereNotNull('average_rating');
        }])
            ->withAvg(['surveyResponses as avg_rating' => function ($query) {
                $query->whereNotNull('average_rating');
            }], 'average_rating')
            ->get()
            ->filter(function ($branch) {
                return $branch->total_ratings > 0;
            })
            ->sortByDesc('avg_rating')
            ->take(10);

        return [
            'datasets' => [
                [
                    'label' => __('Average Rating by Branch'),
                    'data' => $branchRatings->pluck('avg_rating')->map(fn ($rating) => round($rating, 2))->toArray(),
                    'backgroundColor' => $branchRatings->map(function ($branch) {
                        $rating = $branch->avg_rating;
                        if ($rating >= 4.5) {
                            return 'rgb(34, 197, 94)';
                        }   // green
                        if ($rating >= 3.5) {
                            return 'rgb(59, 130, 246)';
                        }  // blue
                        if ($rating >= 2.5) {
                            return 'rgb(107, 114, 128)';
                        } // gray
                        if ($rating >= 1.5) {
                            return 'rgb(245, 158, 11)';
                        }  // orange

                        return 'rgb(239, 68, 68)';                       // red
                    })->toArray(),
                ],
            ],
            'labels' => $branchRatings->map(function ($branch) {
                return $branch->getTranslation('name', app()->getLocale()) ??
                    $branch->getTranslation('name', 'en');
            })->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
