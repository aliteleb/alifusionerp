<?php

namespace Modules\Survey\Filament\Widgets;

use App\Models\Customer;
use App\Models\SurveyResponse;
use App\Services\TenantDatabaseService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class CustomerEngagementWidget extends BaseWidget
{
    protected static ?int $sort = 8;

    protected function getStats(): array
    {
        // Connect to current tenant database
        $facility = Auth::user()?->facility ?? \App\Models\Facility::first();
        if ($facility) {
            TenantDatabaseService::switchToTenant($facility);
        }

        // Active customers (responded in last 30 days)
        $activeCustomers = Customer::whereHas('surveyResponses', function ($query) {
            $query->where('created_at', '>=', now()->subDays(30));
        })->count();

        // Total customers
        $totalCustomers = Customer::count();

        // Engagement rate
        $engagementRate = $totalCustomers > 0 ? ($activeCustomers / $totalCustomers) * 100 : 0;

        // Repeat customers (customers with more than one response)
        $repeatCustomers = Customer::has('surveyResponses', '>', 1)->count();

        // Average responses per customer
        $avgResponsesPerCustomer = $totalCustomers > 0
            ? SurveyResponse::count() / $totalCustomers
            : 0;

        // New customers this week
        $newCustomersThisWeek = Customer::whereBetween('created_at', [
            now()->startOfWeek(),
            now()->endOfWeek(),
        ])->count();

        // Most active customer
        $mostActiveCustomer = Customer::withCount('surveyResponses')
            ->orderByDesc('survey_responses_count')
            ->first();

        return [
            Stat::make(__('Active Customers'), number_format($activeCustomers))
                ->description(__('Responded in last 30 days'))
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),

            Stat::make(__('Engagement Rate'), number_format($engagementRate, 1).'%')
                ->description(__('Active vs total customers'))
                ->descriptionIcon('heroicon-m-chart-bar-square')
                ->color($engagementRate >= 50 ? 'success' : ($engagementRate >= 25 ? 'warning' : 'danger')),

            Stat::make(__('Repeat Customers'), number_format($repeatCustomers))
                ->description(__('Multiple responses'))
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('info'),

            Stat::make(__('Avg Responses/Customer'), number_format($avgResponsesPerCustomer, 1))
                ->description(__('Response frequency'))
                ->descriptionIcon('heroicon-m-calculator')
                ->color($avgResponsesPerCustomer >= 2 ? 'success' : 'warning'),

            Stat::make(__('New This Week'), number_format($newCustomersThisWeek))
                ->description(__('New customer registrations'))
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('primary'),

            Stat::make(__('Most Active'), $mostActiveCustomer ? $mostActiveCustomer->name : __('None'))
                ->description($mostActiveCustomer ? $mostActiveCustomer->survey_responses_count.' '.__('responses') : __('No data'))
                ->descriptionIcon('heroicon-m-trophy')
                ->color('warning'),
        ];
    }
}
