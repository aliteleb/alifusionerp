<?php

namespace Modules\Survey\Filament\Widgets;

use App\Models\SurveyCategory;
use Filament\Widgets\ChartWidget;

class SurveyCategoriesChartWidget extends ChartWidget
{
    public function getHeading(): ?string
    {
        return __('Surveys by Category');
    }

    protected static ?int $sort = 5;

    protected function getData(): array
    {
        try {
            $categories = SurveyCategory::withCount('surveys')->get();

            $labels = $categories->pluck('name')->toArray();
            $data = $categories->pluck('surveys_count')->toArray();

            $colors = [
                '#3b82f6', '#ef4444', '#10b981', '#f59e0b',
                '#8b5cf6', '#06b6d4', '#f97316', '#84cc16',
            ];

            return [
                'datasets' => [
                    [
                        'data' => $data,
                        'backgroundColor' => array_slice($colors, 0, count($data)),
                        'borderWidth' => 2,
                        'borderColor' => '#ffffff',
                    ],
                ],
                'labels' => $labels,
            ];
        } catch (\Exception $e) {
            // Fallback data
            return [
                'datasets' => [
                    [
                        'data' => [12, 8, 15, 6, 9],
                        'backgroundColor' => ['#3b82f6', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6'],
                        'borderWidth' => 2,
                        'borderColor' => '#ffffff',
                    ],
                ],
                'labels' => [__('Customer Satisfaction'), __('Product Feedback'), __('Service Quality'), __('Market Research'), __('Other')],
            ];
        }
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
