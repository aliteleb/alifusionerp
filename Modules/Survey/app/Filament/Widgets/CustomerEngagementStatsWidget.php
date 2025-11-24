<?php

namespace Modules\Survey\Filament\Widgets;

use App\Models\Customer;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CustomerEngagementStatsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        try {
            // Get real data from database
            $totalCustomers = Customer::count();

            // Active customers (responded in last 30 days)
            $activeCustomers = Customer::whereHas('surveyResponses', function ($query) {
                $query->where('created_at', '>=', now()->subDays(30));
            })->count();

            // Engagement rate
            $engagementRate = $totalCustomers > 0 ? round(($activeCustomers / $totalCustomers) * 100, 1) : 0;

            // New customers this month
            $newThisMonth = Customer::whereBetween('created_at', [
                now()->startOfMonth(),
                now()->endOfMonth(),
            ])->count();

            return [
                Stat::make(__('Total Customers'), number_format($totalCustomers))
                    ->description(__('All registered customers'))
                    ->descriptionIcon('heroicon-m-users')
                    ->color('primary'),

                Stat::make(__('Active Customers'), number_format($activeCustomers))
                    ->description(__('Responded in last 30 days'))
                    ->descriptionIcon('heroicon-m-user-group')
                    ->color('success'),

                Stat::make(__('Engagement Rate'), $engagementRate.'%')
                    ->description(__('Active vs total customers'))
                    ->descriptionIcon('heroicon-m-chart-bar')
                    ->color($engagementRate >= 70 ? 'success' : ($engagementRate >= 50 ? 'warning' : 'danger')),

                Stat::make(__('New This Month'), number_format($newThisMonth))
                    ->description(__('New customer registrations'))
                    ->descriptionIcon('heroicon-m-user-plus')
                    ->color('info'),
            ];
        } catch (\Exception $e) {
            return [
                Stat::make(__('Total Customers'), '0')
                    ->description(__('All registered customers'))
                    ->descriptionIcon('heroicon-m-users')
                    ->color('gray'),

                Stat::make(__('Active Customers'), '0')
                    ->description(__('Responded in last 30 days'))
                    ->descriptionIcon('heroicon-m-user-group')
                    ->color('gray'),

                Stat::make(__('Engagement Rate'), '0%')
                    ->description(__('Error loading data'))
                    ->descriptionIcon('heroicon-m-exclamation-triangle')
                    ->color('danger'),

                Stat::make(__('New This Month'), '0')
                    ->description(__('Error loading data'))
                    ->descriptionIcon('heroicon-m-exclamation-triangle')
                    ->color('danger'),
            ];
        }
    }
}
