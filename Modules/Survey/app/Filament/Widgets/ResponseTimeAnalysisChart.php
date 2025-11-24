<?php

namespace Modules\Survey\Filament\Widgets;

use App\Models\SurveyResponse;
use App\Services\TenantDatabaseService;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ResponseTimeAnalysisChart extends ChartWidget
{
    public function getHeading(): ?string
    {
        return __('Response Time Distribution');
    }

    protected static ?int $sort = 10;

    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        // Connect to current tenant database
        $facility = Auth::user()?->facility ?? \App\Models\Facility::first();
        if ($facility) {
            TenantDatabaseService::switchToTenant($facility);
        }

        // Get response time distribution
        $responseTimeData = SurveyResponse::select(
            DB::raw('
                CASE 
                    WHEN duration_seconds <= 60 THEN "0-1 min"
                    WHEN duration_seconds <= 300 THEN "1-5 min"
                    WHEN duration_seconds <= 600 THEN "5-10 min"
                    WHEN duration_seconds <= 1800 THEN "10-30 min"
                    ELSE "30+ min"
                END as time_range
            '),
            DB::raw('COUNT(*) as count')
        )
            ->whereNotNull('duration_seconds')
            ->where('duration_seconds', '>', 0)
            ->groupBy('time_range')
            ->get();

        // Initialize all time ranges
        $timeRanges = [
            '0-1 min' => 0,
            '1-5 min' => 0,
            '5-10 min' => 0,
            '10-30 min' => 0,
            '30+ min' => 0,
        ];

        // Fill with actual data
        foreach ($responseTimeData as $item) {
            $timeRanges[$item->time_range] = $item->count;
        }

        $labels = array_keys($timeRanges);
        $data = array_values($timeRanges);
        $colors = [
            'rgba(34, 197, 94, 0.8)',   // Green - Quick (0-1 min)
            'rgba(59, 130, 246, 0.8)',  // Blue - Normal (1-5 min)
            'rgba(234, 179, 8, 0.8)',   // Yellow - Medium (5-10 min)
            'rgba(249, 115, 22, 0.8)',  // Orange - Slow (10-30 min)
            'rgba(239, 68, 68, 0.8)',   // Red - Very slow (30+ min)
        ];

        return [
            'datasets' => [
                [
                    'label' => __('Responses'),
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderColor' => array_map(fn ($color) => str_replace('0.8', '1', $color), $colors),
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => new \Filament\Support\RawJs('
                            function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.parsed / total) * 100).toFixed(1);
                                return context.label + ": " + context.parsed + " (" + percentage + "%)";
                            }
                        '),
                    ],
                ],
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
        ];
    }
}
