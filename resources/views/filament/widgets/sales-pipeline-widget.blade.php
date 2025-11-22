<x-filament-widgets::widget>
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ __('Sales Pipeline') }}
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Revenue opportunities and performance') }}
                </p>
            </div>
            <div class="flex items-center space-x-4">
                <div class="text-right">
                    <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('Pipeline Value') }}</div>
                    <div class="text-xl font-bold text-gray-900 dark:text-white">
                        ${{ number_format($this->getPipelineData()['total_pipeline_value']) }}
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('Won This Month') }}</div>
                    <div class="text-xl font-bold text-green-600 dark:text-green-400">
                        ${{ number_format($this->getPipelineData()['won_this_month']) }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Pipeline Stages --}}
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            @foreach($this->getPipelineData()['stages'] as $stageKey => $stage)
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-3 h-3 rounded-full" style="background-color: {{ $stage['color'] }}"></div>
                        <div class="text-right">
                            <div class="text-lg font-bold text-gray-900 dark:text-white">{{ $stage['count'] }}</div>
                            <div class="text-xs text-gray-600 dark:text-gray-400">{{ __('deals') }}</div>
                        </div>
                    </div>
                    <div class="text-sm font-medium text-gray-900 dark:text-white mb-1">
                        {{ $stage['name'] }}
                    </div>
                    @if($stage['value'] > 0)
                        <div class="text-xs text-gray-600 dark:text-gray-400">
                            ${{ number_format($stage['value']) }}
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Progress Bar --}}
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ __('Monthly Target Progress') }}</span>
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    {{ number_format(($this->getPipelineData()['won_this_month'] / $this->getPipelineData()['target']) * 100, 1) }}%
                </span>
            </div>
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                <div class="bg-green-600 h-2 rounded-full transition-all duration-300" 
                     style="width: {{ min(($this->getPipelineData()['won_this_month'] / $this->getPipelineData()['target']) * 100, 100) }}%"></div>
            </div>
            <div class="flex justify-between text-xs text-gray-600 dark:text-gray-400 mt-1">
                <span>${{ number_format($this->getPipelineData()['won_this_month']) }}</span>
                <span>${{ number_format($this->getPipelineData()['target']) }}</span>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="flex space-x-3">
            <a href="{{ route('filament.admin.resources.deals.create') }}"
               class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors">
                <x-heroicon-o-plus class="h-4 w-4 mr-2" />
                {{ __('New Deal') }}
            </a>
            <a href="{{ route('filament.admin.resources.clients.create') }}"
               class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                <x-heroicon-o-user-plus class="h-4 w-4 mr-2" />
                {{ __('New Client') }}
            </a>
            <a href="{{ route('filament.admin.pages.deal-reports') }}"
               class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                <x-heroicon-o-chart-bar class="h-4 w-4 mr-2" />
                {{ __('View Reports') }}
            </a>
        </div>
    </div>
</x-filament-widgets::widget>
