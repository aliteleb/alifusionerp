<?php

namespace Modules\Survey\Filament\Widgets;

use App\Models\SurveyCategory;
use App\Services\TenantDatabaseService;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class SurveyCategoryPerformanceChart extends ChartWidget
{
    public function getHeading(): ?string
    {
        return __('Survey Category Performance');
    }

    protected static ?int $sort = 11;

    protected int|string|array $columnSpan = 2;

    protected function getData(): array
    {
        // Connect to current tenant database
        $facility = Auth::user()?->facility ?? \App\Models\Facility::first();
        if ($facility) {
            TenantDatabaseService::switchToTenant($facility);
        }

        // Get categories with survey counts and response data
        $categories = SurveyCategory::withCount(['surveys', 'surveys as active_surveys_count' => function ($query) {
            $query->where('status', 'active');
        }])
            ->with(['surveys' => function ($query) {
                $query->withCount('responses');
            }])
            ->having('surveys_count', '>', 0)
            ->orderByDesc('surveys_count')
            ->limit(8)
            ->get();

        $labels = [];
        $surveyCounts = [];
        $responseCounts = [];
        $colors = [
            'rgba(59, 130, 246, 0.8)',   // Blue
            'rgba(34, 197, 94, 0.8)',    // Green
            'rgba(249, 115, 22, 0.8)',   // Orange
            'rgba(239, 68, 68, 0.8)',    // Red
            'rgba(168, 85, 247, 0.8)',   // Purple
            'rgba(236, 72, 153, 0.8)',   // Pink
            'rgba(14, 165, 233, 0.8)',   // Sky
            'rgba(132, 204, 22, 0.8)',   // Lime
        ];

        foreach ($categories as $index => $category) {
            $categoryName = $category->getTranslation('name', app()->getLocale());
            $labels[] = strlen($categoryName) > 20 ? substr($categoryName, 0, 20).'...' : $categoryName;

            $surveyCounts[] = $category->surveys_count;

            // Count total responses for all surveys in this category
            $totalResponses = $category->surveys->sum('responses_count');
            $responseCounts[] = $totalResponses;
        }

        return [
            'datasets' => [
                [
                    'label' => __('Number of Surveys'),
                    'data' => $surveyCounts,
                    'backgroundColor' => array_slice($colors, 0, count($labels)),
                    'borderColor' => array_map(fn ($color) => str_replace('0.8', '1', $color), array_slice($colors, 0, count($labels))),
                    'borderWidth' => 2,
                    'yAxisID' => 'y',
                ],
                [
                    'label' => __('Total Responses'),
                    'data' => $responseCounts,
                    'type' => 'line',
                    'borderColor' => 'rgba(239, 68, 68, 1)',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'borderWidth' => 3,
                    'fill' => false,
                    'tension' => 0.4,
                    'yAxisID' => 'y1',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
            ],
            'scales' => [
                'y' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => __('Number of Surveys'),
                    ],
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => __('Total Responses'),
                    ],
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                ],
                'x' => [
                    'ticks' => [
                        'maxRotation' => 45,
                    ],
                ],
            ],
            'responsive' => true,
            'interaction' => [
                'intersect' => false,
                'mode' => 'index',
            ],
        ];
    }
}
