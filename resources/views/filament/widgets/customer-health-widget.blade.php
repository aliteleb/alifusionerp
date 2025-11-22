<x-filament-widgets::widget>
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ __('Customer Health') }}
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Client satisfaction and retention metrics') }}
                </p>
            </div>
            <div class="flex items-center space-x-6">
                <div class="text-center">
                    <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('Satisfaction') }}</div>
                    <div class="text-xl font-bold text-green-600 dark:text-green-400">
                        {{ $this->getCustomerHealthData()['satisfaction_score'] }}/5
                    </div>
                </div>
                <div class="text-center">
                    <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('Churn Rate') }}</div>
                    <div class="text-xl font-bold text-red-600 dark:text-red-400">
                        {{ $this->getCustomerHealthData()['churn_rate'] }}%
                    </div>
                </div>
            </div>
        </div>

        {{-- Key Metrics --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('Total Clients') }}</div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ number_format($this->getCustomerHealthData()['total_clients']) }}
                        </div>
                    </div>
                    <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                        <x-heroicon-o-users class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('Active Clients') }}</div>
                        <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                            {{ number_format($this->getCustomerHealthData()['active_clients']) }}
                        </div>
                    </div>
                    <div class="w-10 h-10 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                        <x-heroicon-o-check-circle class="h-5 w-5 text-green-600 dark:text-green-400" />
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('New This Month') }}</div>
                        <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                            {{ number_format($this->getCustomerHealthData()['new_this_month']) }}
                        </div>
                    </div>
                    <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                        <x-heroicon-o-user-plus class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('At Risk') }}</div>
                        <div class="text-2xl font-bold text-red-600 dark:text-red-400">
                            {{ number_format($this->getCustomerHealthData()['at_risk_clients']) }}
                        </div>
                    </div>
                    <div class="w-10 h-10 bg-red-100 dark:bg-red-900 rounded-lg flex items-center justify-center">
                        <x-heroicon-o-exclamation-triangle class="h-5 w-5 text-red-600 dark:text-red-400" />
                    </div>
                </div>
            </div>
        </div>

        {{-- Activity Levels --}}
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
            <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">{{ __('Client Activity Levels') }}</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="text-center">
                    <div class="text-lg font-bold text-green-600 dark:text-green-400">
                        {{ number_format($this->getCustomerHealthData()['activity_levels']['high']) }}
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('High Activity') }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('Last 7 days') }}</div>
                </div>
                <div class="text-center">
                    <div class="text-lg font-bold text-yellow-600 dark:text-yellow-400">
                        {{ number_format($this->getCustomerHealthData()['activity_levels']['medium']) }}
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('Medium Activity') }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('Last 30 days') }}</div>
                </div>
                <div class="text-center">
                    <div class="text-lg font-bold text-red-600 dark:text-red-400">
                        {{ number_format($this->getCustomerHealthData()['activity_levels']['low']) }}
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('Low Activity') }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('30+ days ago') }}</div>
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="flex space-x-3">
            <a href="{{ route('filament.admin.resources.clients.index', ['tableFilters[is_active][value]' => 'false']) }}"
               class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors">
                <x-heroicon-o-exclamation-triangle class="h-4 w-4 mr-2" />
                {{ __('View At-Risk Clients') }}
            </a>
            <a href="{{ route('filament.admin.resources.clients.create') }}"
               class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                <x-heroicon-o-user-plus class="h-4 w-4 mr-2" />
                {{ __('Add New Client') }}
            </a>
            <a href="{{ route('filament.admin.pages.client-reports') }}"
               class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                <x-heroicon-o-chart-bar class="h-4 w-4 mr-2" />
                {{ __('Client Reports') }}
            </a>
        </div>
    </div>
</x-filament-widgets::widget>
