<?php

namespace Modules\Survey\Filament\Widgets;

use App\Models\SurveyQuestion;
use App\Services\TenantDatabaseService;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class QuestionPerformanceChart extends ChartWidget
{
    public function getHeading(): ?string
    {
        return __('Question Response Rate');
    }

    protected static ?int $sort = 9;

    protected int|string|array $columnSpan = 2;

    protected function getData(): array
    {
        // Connect to current tenant database
        $facility = Auth::user()?->facility ?? \App\Models\Facility::first();
        if ($facility) {
            TenantDatabaseService::switchToTenant($facility);
        }

        // Get top 10 questions with their response rates
        $questions = SurveyQuestion::with(['survey', 'questionResponses'])
            ->withCount([
                'questionResponses',
                'questionResponses as answered_count' => function ($query) {
                    $query->where('is_skipped', false);
                },
            ])
            ->having('question_responses_count', '>', 0)
            ->orderByDesc('question_responses_count')
            ->limit(10)
            ->get();

        $labels = [];
        $responseRates = [];
        $skipRates = [];

        foreach ($questions as $question) {
            $questionText = $question->getTranslation('question_text', app()->getLocale());
            $surveyTitle = $question->survey ? $question->survey->getTranslation('title', app()->getLocale()) : 'Unknown';

            // Create a short label
            $shortQuestion = strlen($questionText) > 30 ? substr($questionText, 0, 30).'...' : $questionText;
            $labels[] = "Q{$question->order}: {$shortQuestion}";

            // Calculate rates
            $totalResponses = $question->question_responses_count;
            $answeredResponses = $question->answered_count;
            $skippedResponses = $totalResponses - $answeredResponses;

            $responseRate = $totalResponses > 0 ? ($answeredResponses / $totalResponses) * 100 : 0;
            $skipRate = $totalResponses > 0 ? ($skippedResponses / $totalResponses) * 100 : 0;

            $responseRates[] = round($responseRate, 1);
            $skipRates[] = round($skipRate, 1);
        }

        return [
            'datasets' => [
                [
                    'label' => __('Response Rate (%)'),
                    'data' => $responseRates,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.8)',
                    'borderColor' => 'rgba(34, 197, 94, 1)',
                    'borderWidth' => 2,
                ],
                [
                    'label' => __('Skip Rate (%)'),
                    'data' => $skipRates,
                    'backgroundColor' => 'rgba(239, 68, 68, 0.6)',
                    'borderColor' => 'rgba(239, 68, 68, 1)',
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
                    'display' => true,
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
                        'minRotation' => 45,
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
