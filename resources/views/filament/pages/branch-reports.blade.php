<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Header Section --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-xl font-semibold text-gray-900 dark:text-white">
                    {{ __('Branch Reports') }}
                </h1>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Analyze branch data and performance') }}
                </p>
            </div>
        </div>

            {{-- Filters --}}
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-800 dark:to-gray-700 border border-blue-200 dark:border-gray-600 rounded-xl p-6 mb-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                            <x-heroicon-o-funnel class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Filters') }}</h3>
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        {{ __('Customize your report view') }}
                    </div>
                </div>
                
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
                    {{-- Status Filter --}}
                    <div class="space-y-2">
                        <label class="flex items-center space-x-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                            <x-heroicon-o-check-circle class="h-4 w-4" />
                            <span>{{ __('Status') }}</span>
                        </label>
                        <x-filament::input.wrapper>
                            <x-filament::input.select wire:model="status" placeholder="{{ __('All Statuses') }}">
                                <option value="active">{{ __('Active') }}</option>
                                <option value="inactive">{{ __('Inactive') }}</option>
                                <option value="hq">{{ __('Headquarters') }}</option>
                                <option value="all">{{ __('All') }}</option>
                            </x-filament::input.select>
                        </x-filament::input.wrapper>
                    </div>

                    {{-- From Date Filter --}}
                    <div class="space-y-2">
                        <label class="flex items-center space-x-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                            <x-heroicon-o-calendar-days class="h-4 w-4" />
                            <span>{{ __('From Date') }}</span>
                        </label>
                        <x-filament::input.wrapper>
                            <x-filament::input 
                                type="date" 
                                wire:model="created_from" 
                                placeholder="{{ __('Select start date') }}"
                            />
                        </x-filament::input.wrapper>
                    </div>

                    {{-- To Date Filter --}}
                    <div class="space-y-2">
                        <label class="flex items-center space-x-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                            <x-heroicon-o-calendar-days class="h-4 w-4" />
                            <span>{{ __('To Date') }}</span>
                        </label>
                        <x-filament::input.wrapper>
                            <x-filament::input 
                                type="date" 
                                wire:model="created_to" 
                                placeholder="{{ __('Select end date') }}"
                            />
                        </x-filament::input.wrapper>
                    </div>

                    {{-- Apply Filters Button --}}
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            <div class="flex items-center space-x-1">
                                <x-heroicon-o-funnel class="h-4 w-4" />
                                <span>{{ __('Actions') }}</span>
                            </div>
                        </label>
                        <x-filament::button 
                            wire:click="applyFilters" 
                            color="primary" 
                            size="sm"
                            class="w-full"
                        >
                            <x-heroicon-o-funnel class="h-4 w-4 me-2" />
                            {{ __('Apply Filters') }}
                        </x-filament::button>
                    </div>
                </div>
            </div>

        {{-- Statistics Cards --}}
        @php
            $reportData = $this->getReportData();
        @endphp
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded p-4">
                <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('Total Branches') }}</div>
                <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($reportData['total_branches']) }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded p-4">
                <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('Active Branches') }}</div>
                <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($reportData['active_branches']) }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded p-4">
                <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('Inactive Branches') }}</div>
                <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($reportData['inactive_branches']) }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded p-4">
                <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('Headquarters') }}</div>
                <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($reportData['hq_branches']) }}</div>
            </div>
        </div>

        {{-- Charts Section --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            {{-- Branch Status Chart --}}
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Branch Status Distribution') }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Overview of branch statuses') }}</p>
                </div>
                <div class="p-6">
                    <div class="h-64">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>

            {{-- Monthly Branch Creation Chart --}}
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Monthly Branch Creation') }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Branches created over time') }}</p>
                </div>
                <div class="p-6">
                    <div class="h-64">
                        <canvas id="monthlyChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Branch Data Table --}}
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Branch Data') }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Detailed branch information') }}</p>
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        {{ __('Showing') }} {{ $reportData['branches']->firstItem() ?? 0 }} {{ __('to') }} {{ $reportData['branches']->lastItem() ?? 0 }} {{ __('of') }} {{ $reportData['branches']->total() }} {{ __('results') }}
                    </div>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-4 py-3 text-left rtl:text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Name') }}</th>
                            <th class="px-4 py-3 text-left rtl:text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Status') }}</th>
                            <th class="px-4 py-3 text-left rtl:text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Type') }}</th>
                            <th class="px-4 py-3 text-left rtl:text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Users Count') }}</th>
                            <th class="px-4 py-3 text-left rtl:text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Created At') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800">
                        @forelse($reportData['branches'] as $branch)
                            <tr class="even:bg-gray-50 dark:even:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center me-3">
                                            <x-heroicon-o-building-office-2 class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                                        </div>
                                        <div class="font-medium">{{ $branch->name }}</div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    @if($branch->is_active)
                                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                            {{ __('Active') }}
                                        </span>
                                    @else
                                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                            {{ __('Inactive') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if($branch->is_hq)
                                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                            {{ __('Headquarters') }}
                                        </span>
                                    @else
                                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">
                                            {{ __('Branch') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                                    {{ $branch->users()->count() }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $branch->created_at->format('Y-m-d H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                    {{ __('No branches found matching your criteria.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($reportData['branches']->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="text-sm text-gray-700 dark:text-gray-300">
                                {{ __('Showing') }} {{ $reportData['branches']->firstItem() }} {{ __('to') }} {{ $reportData['branches']->lastItem() }} {{ __('of') }} {{ $reportData['branches']->total() }} {{ __('results') }}
                            </div>
                            
                            {{-- Per Page Selector --}}
                            <div class="flex items-center space-x-2">
                                <label class="text-sm text-gray-700 dark:text-gray-300">{{ __('Per page:') }}</label>
                                <select wire:model="perPage" class="text-sm border border-gray-300 dark:border-gray-600 rounded px-2 py-1 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                    <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="flex items-center space-x-2">
                            {{-- Previous Button --}}
                            @if($reportData['branches']->onFirstPage())
                                <span class="px-3 py-1 text-sm text-gray-400 bg-gray-100 dark:bg-gray-700 rounded cursor-not-allowed">
                                    {{ __('Previous') }}
                                </span>
                            @else
                                <a href="{{ $reportData['branches']->previousPageUrl() }}" class="px-3 py-1 text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-gray-700">
                                    {{ __('Previous') }}
                                </a>
                            @endif

                            {{-- Page Numbers --}}
                            @php
                                $currentPage = $reportData['branches']->currentPage();
                                $lastPage = $reportData['branches']->lastPage();
                                $startPage = max(1, $currentPage - 2);
                                $endPage = min($lastPage, $currentPage + 2);
                            @endphp

                            @if($startPage > 1)
                                <a href="{{ $reportData['branches']->url(1) }}" class="px-3 py-1 text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-gray-700">1</a>
                                @if($startPage > 2)
                                    <span class="px-2 text-gray-400">...</span>
                                @endif
                            @endif

                            @for($page = $startPage; $page <= $endPage; $page++)
                                @if($page == $currentPage)
                                    <span class="px-3 py-1 text-sm text-white bg-blue-600 rounded">{{ $page }}</span>
                                @else
                                    <a href="{{ $reportData['branches']->url($page) }}" class="px-3 py-1 text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-gray-700">{{ $page }}</a>
                                @endif
                            @endfor

                            @if($endPage < $lastPage)
                                @if($endPage < $lastPage - 1)
                                    <span class="px-2 text-gray-400">...</span>
                                @endif
                                <a href="{{ $reportData['branches']->url($lastPage) }}" class="px-3 py-1 text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-gray-700">{{ $lastPage }}</a>
                            @endif

                            {{-- Next Button --}}
                            @if($reportData['branches']->hasMorePages())
                                <a href="{{ $reportData['branches']->nextPageUrl() }}" class="px-3 py-1 text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-gray-700">
                                    {{ __('Next') }}
                                </a>
                            @else
                                <span class="px-3 py-1 text-sm text-gray-400 bg-gray-100 dark:bg-gray-700 rounded cursor-not-allowed">
                                    {{ __('Next') }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script>
        // Chart data from PHP
        // Initial chart data
        window.branchChartData = @json($this->chartData);

        // Chart instances storage - using window scope to avoid conflicts
        window.branchStatusChart = null;
        window.branchMonthlyChart = null;

        // Function to create charts
        function createCharts(chartData = null) {
            // Use provided chart data or keep existing data
            if (chartData) {
                window.branchChartData = chartData;
            }
            
            // Destroy existing charts if they exist
            if (window.branchStatusChart) window.branchStatusChart.destroy();
            if (window.branchMonthlyChart) window.branchMonthlyChart.destroy();

            // Status Chart (Doughnut)
            const statusCanvas = document.getElementById('statusChart');
            if (!statusCanvas) return; // Exit if canvas not found
            
            const statusCtx = statusCanvas.getContext('2d');
            window.branchStatusChart = new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: [
                        '{{ __("Active") }}', 
                        '{{ __("Inactive") }}',
                        '{{ __("Headquarters") }}'
                    ],
                    datasets: [{
                        data: [
                            window.branchChartData.status.active, 
                            window.branchChartData.status.inactive,
                            window.branchChartData.status.hq
                        ],
                        backgroundColor: [
                            '#10B981', // Green for active
                            '#EF4444', // Red for inactive
                            '#8B5CF6'  // Purple for HQ
                        ],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true,
                                font: {
                                    size: 12
                                }
                            }
                        }
                    }
                }
            });

            // Monthly Branch Creation Chart (Line)
            const monthlyCanvas = document.getElementById('monthlyChart');
            if (!monthlyCanvas) return; // Exit if canvas not found
            
            const monthlyCtx = monthlyCanvas.getContext('2d');
            
            const monthlyLabels = window.branchChartData.monthly && window.branchChartData.monthly.length > 0 ? 
                window.branchChartData.monthly.map(item => item.month) : 
                ['{{ __("No Data") }}'];
            const monthlyValues = window.branchChartData.monthly && window.branchChartData.monthly.length > 0 ? 
                window.branchChartData.monthly.map(item => item.count) : 
                [0];
            
            window.branchMonthlyChart = new Chart(monthlyCtx, {
                type: 'line',
                data: {
                    labels: monthlyLabels,
                    datasets: [{
                        label: '{{ __("New Branches") }}',
                        data: monthlyValues,
                        borderColor: '#8B5CF6',
                        backgroundColor: 'rgba(139, 92, 246, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#8B5CF6',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            },
                            ticks: {
                                stepSize: 1
                            }
                        },
                        x: {
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            }
                        }
                    }
                }
            });
        }

        // Function to change per page
        function changePerPage(perPage) {
            const url = new URL(window.location);
            url.searchParams.set('per_page', perPage);
            url.searchParams.delete('page'); // Reset to first page
            window.location.href = url.toString();
        }

        // Initialize charts when page loads
        document.addEventListener('DOMContentLoaded', function() {
            createCharts();
        });

        // Recreate charts when Livewire updates (for SPA navigation)
        document.addEventListener('livewire:navigated', function() {
            createCharts();
        });

        // Recreate charts when filters are applied - improved event handling
        document.addEventListener('livewire:init', () => {
            Livewire.on('recreate-charts', (event) => {
                console.log('Chart recreation event received:', event);
                createCharts(event.chartData);
            });
        });

        // Additional event listener for Livewire updates
        document.addEventListener('livewire:updated', function() {
            // Small delay to ensure DOM is updated
            setTimeout(() => {
                createCharts();
            }, 100);
        });

        // Listen for Livewire component updates specifically
        document.addEventListener('livewire:updated', function(event) {
            // Check if this is a component update that might affect charts
            if (event.detail && event.detail.component) {
                setTimeout(() => {
                    createCharts();
                }, 150);
            }
        });
    </script>
</x-filament-panels::page>
