<?php

namespace Modules\Survey\Filament\Widgets;

use App\Models\SurveyResponse;
use App\Services\TenantDatabaseService;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class MonthlyResponseComparisonChart extends ChartWidget
{
    public function getHeading(): ?string
    {
        return __('Monthly Response Comparison');
    }

    protected static ?int $sort = 7;

    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        // Connect to current tenant database
        $facility = Auth::user()?->facility ?? \App\Models\Facility::first();
        if ($facility) {
            TenantDatabaseService::switchToTenant($facility);
        }

        // Get last 12 months data
        $currentYearData = [];
        $previousYearData = [];
        $labels = [];

        for ($i = 11; $i >= 0; $i--) {
            $currentMonth = now()->subMonths($i);
            $previousMonth = now()->subMonths($i + 12);

            $labels[] = $currentMonth->format('M Y');

            // Current year data
            $currentCount = SurveyResponse::whereYear('created_at', $currentMonth->year)
                ->whereMonth('created_at', $currentMonth->month)
                ->count();
            $currentYearData[] = $currentCount;

            // Previous year data (for comparison)
            $previousCount = SurveyResponse::whereYear('created_at', $previousMonth->year)
                ->whereMonth('created_at', $previousMonth->month)
                ->count();
            $previousYearData[] = $previousCount;
        }

        return [
            'datasets' => [
                [
                    'label' => now()->year.' '.__('Responses'),
                    'data' => $currentYearData,
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => false,
                    'tension' => 0.4,
                ],
                [
                    'label' => (now()->year - 1).' '.__('Responses'),
                    'data' => $previousYearData,
                    'borderColor' => 'rgb(156, 163, 175)',
                    'backgroundColor' => 'rgba(156, 163, 175, 0.1)',
                    'fill' => false,
                    'tension' => 0.4,
                    'borderDash' => [5, 5],
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
                'x' => [
                    'ticks' => [
                        'maxRotation' => 45,
                    ],
                ],
            ],
            'interaction' => [
                'intersect' => false,
                'mode' => 'index',
            ],
        ];
    }
}
