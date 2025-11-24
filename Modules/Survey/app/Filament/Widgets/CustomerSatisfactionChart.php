<?php

namespace Modules\Survey\Filament\Widgets;

use App\Models\SurveyResponse;
use App\Services\TenantDatabaseService;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CustomerSatisfactionChart extends ChartWidget
{
    public function getHeading(): ?string
    {
        return __('Customer Satisfaction Distribution');
    }

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        // Connect to current tenant database
        $facility = Auth::user()?->facility ?? \App\Models\Facility::first();
        if ($facility) {
            TenantDatabaseService::switchToTenant($facility);
        }

        // Get rating distribution
        $ratingData = SurveyResponse::select(
            DB::raw('FLOOR(average_rating) as rating_floor'),
            DB::raw('COUNT(*) as count')
        )
            ->whereNotNull('average_rating')
            ->groupBy('rating_floor')
            ->orderBy('rating_floor')
            ->get();

        $labels = [];
        $data = [];
        $colors = [
            'rgba(239, 68, 68, 0.8)',   // 1 star - Red
            'rgba(249, 115, 22, 0.8)',  // 2 stars - Orange
            'rgba(234, 179, 8, 0.8)',   // 3 stars - Yellow
            'rgba(59, 130, 246, 0.8)',  // 4 stars - Blue
            'rgba(34, 197, 94, 0.8)',   // 5 stars - Green
        ];

        // Initialize all ratings from 1-5
        for ($i = 1; $i <= 5; $i++) {
            $labels[] = $i.' '.__('Star'.($i > 1 ? 's' : ''));
            $data[] = 0;
        }

        // Fill with actual data
        foreach ($ratingData as $item) {
            $index = (int) $item->rating_floor - 1;
            if ($index >= 0 && $index < 5) {
                $data[$index] = $item->count;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => __('Responses'),
                    'data' => $data,
                    'backgroundColor' => array_slice($colors, 0, count($data)),
                    'borderColor' => array_map(fn ($color) => str_replace('0.8', '1', $color), array_slice($colors, 0, count($data))),
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'right',
                ],
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
        ];
    }
}
