<?php

namespace Modules\Survey\Filament\Widgets;

use App\Models\Survey;
use App\Services\TenantDatabaseService;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class SurveyCompletionRateChart extends ChartWidget
{
    public function getHeading(): ?string
    {
        return __('Survey Completion Rates');
    }

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 2;

    protected function getData(): array
    {
        // Connect to current tenant database
        $facility = Auth::user()?->facility ?? \App\Models\Facility::first();
        if ($facility) {
            TenantDatabaseService::switchToTenant($facility);
        }

        $surveys = Survey::withCount(['responses' => function ($query) {
            $query->where('is_complete', true);
        }])
            ->withCount('responses as total_responses_count')
            ->having('total_responses_count', '>', 0)
            ->limit(10)
            ->get();

        $labels = [];
        $completionRates = [];
        $colors = [];

        foreach ($surveys as $survey) {
            $title = $survey->getTranslation('title', app()->getLocale());
            $labels[] = strlen($title) > 25 ? substr($title, 0, 25).'...' : $title;

            $completionRate = $survey->total_responses_count > 0
                ? round(($survey->responses_count / $survey->total_responses_count) * 100, 1)
                : 0;

            $completionRates[] = $completionRate;

            // Color based on completion rate
            if ($completionRate >= 80) {
                $colors[] = 'rgba(34, 197, 94, 0.8)'; // Green
            } elseif ($completionRate >= 60) {
                $colors[] = 'rgba(249, 115, 22, 0.8)'; // Orange
            } else {
                $colors[] = 'rgba(239, 68, 68, 0.8)'; // Red
            }
        }

        return [
            'datasets' => [
                [
                    'label' => __('Completion Rate (%)'),
                    'data' => $completionRates,
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
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'max' => 100,
                    'ticks' => [
                        'callback' => new \Filament\Support\RawJs('function(value) { return value + "%"; }'),
                    ],
                ],
                'x' => [
                    'ticks' => [
                        'maxRotation' => 45,
                    ],
                ],
            ],
        ];
    }
}
