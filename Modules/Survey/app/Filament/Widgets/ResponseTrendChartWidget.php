<?php

namespace Modules\Survey\Filament\Widgets;

use App\Models\SurveyResponse;
use App\Services\TenantDatabaseService;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class ResponseTrendChartWidget extends ChartWidget
{
    public function getHeading(): ?string
    {
        return __('Survey Responses Trend');
    }

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        try {
            // Connect to current tenant database
            $facility = Auth::user()?->facility ?? \App\Models\Facility::first();
            if ($facility) {
                TenantDatabaseService::switchToTenant($facility);
            }

            // Get responses for the last 7 days
            $data = [];
            $labels = [];

            try {
                for ($i = 6; $i >= 0; $i--) {
                    $date = now()->subDays($i);
                    $labels[] = $date->format('M d');

                    $count = SurveyResponse::whereDate('created_at', $date->toDateString())->count();
                    $data[] = $count;
                }
            } catch (\Exception $e) {
                report($e);
                // Fallback data
                $labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                $data = [0, 0, 0, 0, 0, 0, 0];
            }

            return [
                'datasets' => [
                    [
                        'label' => __('Survey Responses'),
                        'data' => $data,
                        'borderColor' => '#3b82f6',
                        'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                        'fill' => true,
                    ],
                ],
                'labels' => $labels,
            ];
        } catch (\Exception $e) {
            report($e);

            return [
                'datasets' => [
                    [
                        'label' => __('Survey Responses'),
                        'data' => [0, 0, 0, 0, 0, 0, 0],
                        'borderColor' => '#ef4444',
                        'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    ],
                ],
                'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            ];
        }
    }

    protected function getType(): string
    {
        return 'line';
    }
}
