<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Header Section --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-xl font-semibold text-gray-900 dark:text-white">
                    {{ __('Contract Reports') }}
                </h1>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Analyze contract data and performance') }}
                </p>
            </div>
            <div class="flex space-x-2">
                @php
                    $exportParams = array_filter([
                        'branch_id' => request('branch_id'),
                        'status' => request('status'),
                        'created_from' => request('created_from'),
                        'created_to' => request('created_to'),
                    ]);
                @endphp
                <a href="{{ route('reports.export', array_merge(['type' => 'contract', 'format' => 'pdf'], $exportParams)) }}" 
                   class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-gray-700">
                    {{ __('PDF') }}
                </a>
                <a href="{{ route('reports.export', array_merge(['type' => 'contract', 'format' => 'excel'], $exportParams)) }}" 
                   class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-gray-700">
                    {{ __('Excel') }}
                </a>
                <a href="{{ route('reports.export', array_merge(['type' => 'contract', 'format' => 'csv'], $exportParams)) }}" 
                   class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-gray-700">
                    {{ __('CSV') }}
                </a>
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
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                {{-- Branch Filter --}}
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        <div class="flex items-center space-x-1">
                            <x-heroicon-o-building-office-2 class="h-4 w-4" />
                            <span>{{ __('Branch') }}</span>
                        </div>
                    </label>
                    <x-filament::input.wrapper>
                        <x-filament::input.select wire:model="branch_id" placeholder="{{ __('All Branches') }}">
                            <option value="">{{ __('All Branches') }}</option>
                            @foreach(\App\Core\Models\Branch::all()->sortBy('name') as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>

                {{-- Status Filter --}}
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        <div class="flex items-center space-x-1">
                            <x-heroicon-o-check-circle class="h-4 w-4" />
                            <span>{{ __('Status') }}</span>
                        </div>
                    </label>
                    <x-filament::input.wrapper>
                        <x-filament::input.select wire:model="status" placeholder="{{ __('All Statuses') }}">
                            <option value="active">{{ __('Active') }}</option>
                            <option value="expired">{{ __('Expired') }}</option>
                            <option value="pending">{{ __('Pending') }}</option>
                            <option value="terminated">{{ __('Terminated') }}</option>
                            <option value="all">{{ __('All') }}</option>
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>

                {{-- Date From --}}
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        <div class="flex items-center space-x-1">
                            <x-heroicon-o-calendar-days class="h-4 w-4" />
                            <span>{{ __('From Date') }}</span>
                        </div>
                    </label>
                    <x-filament::input.wrapper>
                        <x-filament::input 
                            type="date" 
                            wire:model="created_from" 
                            placeholder="{{ __('Select start date') }}"
                        />
                    </x-filament::input.wrapper>
                </div>

                {{-- Date To --}}
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        <div class="flex items-center space-x-1">
                            <x-heroicon-o-calendar-days class="h-4 w-4" />
                            <span>{{ __('To Date') }}</span>
                        </div>
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
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded p-4">
                <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('Total Contracts') }}</div>
                <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($reportData['total_contracts']) }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded p-4">
                <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('Active Contracts') }}</div>
                <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($reportData['active_contracts']) }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded p-4">
                <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('Expired Contracts') }}</div>
                <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($reportData['expired_contracts']) }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded p-4">
                <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('Pending Contracts') }}</div>
                <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($reportData['pending_contracts']) }}</div>
            </div>
        </div>

        {{-- Charts Section --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            {{-- Contract Status Chart --}}
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-white">{{ __('Contract Status Distribution') }}</h2>
                </div>
                <div class="p-6">
                    <canvas id="statusChart" width="400" height="200"></canvas>
                </div>
            </div>

            {{-- Branch Distribution Chart --}}
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-white">{{ __('Contracts by Branch') }}</h2>
                </div>
                <div class="p-6">
                    <canvas id="branchChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>

        {{-- Monthly Registration Chart --}}
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded mb-6">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-medium text-gray-900 dark:text-white">{{ __('Monthly Contract Creation') }}</h2>
            </div>
            <div class="p-6">
                <canvas id="monthlyChart" width="800" height="300"></canvas>
            </div>
        </div>

        {{-- Contract Data Table --}}
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-medium text-gray-900 dark:text-white">{{ __('Contract Data') }}</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-4 py-3 text-left rtl:text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Title') }}</th>
                            <th class="px-4 py-3 text-left rtl:text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Client') }}</th>
                            <th class="px-4 py-3 text-left rtl:text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Branch') }}</th>
                            <th class="px-4 py-3 text-left rtl:text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Status') }}</th>
                            <th class="px-4 py-3 text-left rtl:text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Value') }}</th>
                            <th class="px-4 py-3 text-left rtl:text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Created At') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800">
                        @forelse($reportData['contracts'] as $contract)
                            <tr class="even:bg-gray-50 dark:even:bg-gray-700">
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white text-left rtl:text-right">{{ $contract->title }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300 text-left rtl:text-right">
                                    @if($contract->client)
                                        {{ $contract->client->name }}
                                    @else
                                        <span class="inline-flex px-2 py-1 text-xs font-medium text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 rounded">
                                            {{ __('No Client') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300 text-left rtl:text-right">
                                    @if($contract->branch)
                                        {{ $contract->branch->name }}
                                    @else
                                        <span class="inline-flex px-2 py-1 text-xs font-medium text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 rounded">
                                            {{ __('No Branch') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-left rtl:text-right">
                                    @if($contract->status)
                                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full {{ $contract->status->getColor() }}">
                                            {{ $contract->status->getLabel() }}
                                        </span>
                                    @else
                                        <span class="inline-flex px-2 py-1 text-xs font-medium text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 rounded">
                                            {{ __('No Status') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300 text-left rtl:text-right">
                                    @if($contract->value)
                                        {{ number_format($contract->value, 2) }} {{ $contract->currency ?? 'USD' }}
                                    @else
                                        <span class="text-gray-400">{{ __('N/A') }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300 text-left rtl:text-right">
                                    @if($contract->created_at)
                                        {{ $contract->created_at->format('Y-m-d H:i') }}
                                    @else
                                        <span class="text-gray-400">{{ __('N/A') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                    {{ __('No contracts found matching the criteria.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{-- Pagination --}}
            @if(method_exists($reportData['contracts'], 'hasPages') && $reportData['contracts']->hasPages())
                <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="text-sm text-gray-700 dark:text-gray-300">
                                @if(method_exists($reportData['contracts'], 'firstItem'))
                                    {{ __('Showing :first to :last of :total results', [
                                        'first' => $reportData['contracts']->firstItem(),
                                        'last' => $reportData['contracts']->lastItem(),
                                        'total' => number_format($reportData['contracts']->total())
                                    ]) }}
                                @else
                                    {{ __('Showing :count results', ['count' => $reportData['contracts']->count()]) }}
                                @endif
                            </div>
                            
                            {{-- Per Page Selector --}}
                            <div class="flex items-center space-x-2">
                                <label class="text-sm text-gray-700 dark:text-gray-300">{{ __('Per page:') }}</label>
                                <select onchange="changePerPage(this.value)" class="text-sm border border-gray-300 dark:border-gray-600 rounded px-2 py-1 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                    <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                                </select>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            {{-- Previous Page --}}
                            @if(method_exists($reportData['contracts'], 'onFirstPage') && $reportData['contracts']->onFirstPage())
                                <span class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-gray-400 bg-gray-100 dark:bg-gray-700 rounded cursor-not-allowed">
                                    <x-heroicon-o-chevron-left class="h-4 w-4 me-1 rtl:rotate-180" />
                                    {{ __('Previous') }}
                                </span>
                            @elseif(method_exists($reportData['contracts'], 'previousPageUrl'))
                                <a href="{{ $reportData['contracts']->previousPageUrl() }}" 
                                   class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <x-heroicon-o-chevron-left class="h-4 w-4 me-1 rtl:rotate-180" />
                                    {{ __('Previous') }}
                                </a>
                            @endif

                            {{-- Page Numbers --}}
                            @if(method_exists($reportData['contracts'], 'currentPage'))
                                @php
                                    $currentPage = $reportData['contracts']->currentPage();
                                    $lastPage = $reportData['contracts']->lastPage();
                                    $startPage = max(1, $currentPage - 2);
                                    $endPage = min($lastPage, $currentPage + 2);
                                @endphp
                            
                                {{-- Show first page if not in range --}}
                                @if($startPage > 1)
                                    <a href="{{ $reportData['contracts']->url(1) }}" 
                                       class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-gray-700">
                                        1
                                    </a>
                                    @if($startPage > 2)
                                        <span class="inline-flex items-center px-2 py-1.5 text-sm font-medium text-gray-500 dark:text-gray-400">
                                            ...
                                        </span>
                                    @endif
                                @endif
                                
                                {{-- Show page range --}}
                                @for($page = $startPage; $page <= $endPage; $page++)
                                    @if($page == $currentPage)
                                        <span class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-white bg-blue-600 rounded">
                                            {{ $page }}
                                        </span>
                                    @else
                                        <a href="{{ $reportData['contracts']->url($page) }}" 
                                           class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-gray-700">
                                            {{ $page }}
                                        </a>
                                    @endif
                                @endfor
                                
                                {{-- Show last page if not in range --}}
                                @if($endPage < $lastPage)
                                    @if($endPage < $lastPage - 1)
                                        <span class="inline-flex items-center px-2 py-1.5 text-sm font-medium text-gray-500 dark:text-gray-400">
                                            ...
                                        </span>
                                    @endif
                                    <a href="{{ $reportData['contracts']->url($lastPage) }}" 
                                       class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-gray-700">
                                        {{ $lastPage }}
                                    </a>
                                @endif

                                {{-- Next Page --}}
                                @if(method_exists($reportData['contracts'], 'hasMorePages') && $reportData['contracts']->hasMorePages())
                                    <a href="{{ $reportData['contracts']->nextPageUrl() }}" 
                                       class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-gray-700">
                                        {{ __('Next') }}
                                        <x-heroicon-o-chevron-right class="h-4 w-4 ms-1 rtl:rotate-180" />
                                    </a>
                                @else
                                    <span class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-gray-400 bg-gray-100 dark:bg-gray-700 rounded cursor-not-allowed">
                                        {{ __('Next') }}
                                        <x-heroicon-o-chevron-right class="h-4 w-4 ms-1 rtl:rotate-180" />
                                    </span>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <script>
            // Chart data from PHP
            window.contractChartData = @json($this->chartData);

            // Chart instances storage
            window.contractStatusChart = null;
            window.contractBranchChart = null;
            window.contractMonthlyChart = null;

            // Function to create charts
            function createCharts(chartData = null) {
                if (chartData) {
                    window.contractChartData = chartData;
                }
                
                // Destroy existing charts if they exist
                if (window.contractStatusChart) window.contractStatusChart.destroy();
                if (window.contractBranchChart) window.contractBranchChart.destroy();
                if (window.contractMonthlyChart) window.contractMonthlyChart.destroy();

                // Status Chart (Doughnut)
                const statusCanvas = document.getElementById('statusChart');
                if (statusCanvas) {
                    const statusCtx = statusCanvas.getContext('2d');
                    window.contractStatusChart = new Chart(statusCtx, {
                        type: 'doughnut',
                        data: {
                            labels: ['{{ __("Active") }}', '{{ __("Expired") }}', '{{ __("Pending") }}', '{{ __("Terminated") }}'],
                            datasets: [{
                                data: [
                                    window.contractChartData.status?.active || 0,
                                    window.contractChartData.status?.expired || 0,
                                    window.contractChartData.status?.pending || 0,
                                    window.contractChartData.status?.terminated || 0
                                ],
                                backgroundColor: ['#10B981', '#EF4444', '#F59E0B', '#6B7280'],
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
                                        usePointStyle: true
                                    }
                                }
                            }
                        }
                    });
                }

                // Branch Chart (Bar)
                const branchCanvas = document.getElementById('branchChart');
                if (branchCanvas) {
                    const branchCtx = branchCanvas.getContext('2d');
                    const branchLabels = Object.keys(window.contractChartData.branches || {});
                    const branchValues = Object.values(window.contractChartData.branches || {});
                    
                    window.contractBranchChart = new Chart(branchCtx, {
                        type: 'bar',
                        data: {
                            labels: branchLabels.map(label => label || '{{ __("N/A") }}'),
                            datasets: [{
                                label: '{{ __("Contracts") }}',
                                data: branchValues,
                                backgroundColor: '#3B82F6',
                                borderColor: '#1D4ED8',
                                borderWidth: 1
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
                                    ticks: {
                                        stepSize: 1
                                    }
                                }
                            }
                        }
                    });
                }

                // Monthly Registration Chart (Line)
                const monthlyCanvas = document.getElementById('monthlyChart');
                if (monthlyCanvas) {
                    const monthlyCtx = monthlyCanvas.getContext('2d');
                    
                    const monthlyLabels = window.contractChartData.monthly && window.contractChartData.monthly.length > 0 ? 
                        window.contractChartData.monthly.map(item => item.month) : 
                        ['{{ __("No Data") }}'];
                    const monthlyValues = window.contractChartData.monthly && window.contractChartData.monthly.length > 0 ? 
                        window.contractChartData.monthly.map(item => item.count) : 
                        [0];
                    
                    window.contractMonthlyChart = new Chart(monthlyCtx, {
                        type: 'line',
                        data: {
                            labels: monthlyLabels,
                            datasets: [{
                                label: '{{ __("New Contracts") }}',
                                data: monthlyValues,
                                borderColor: '#8B5CF6',
                                backgroundColor: 'rgba(139, 92, 246, 0.1)',
                                borderWidth: 3,
                                fill: true,
                                tension: 0.4
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
                                    ticks: {
                                        stepSize: 1
                                    }
                                }
                            }
                        }
                    });
                }
            }

            // Initialize charts when page loads
            document.addEventListener('DOMContentLoaded', function() {
                createCharts();
            });

            // Recreate charts when Livewire updates
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

            // Function to change per page count
            function changePerPage(perPage) {
                const url = new URL(window.location);
                url.searchParams.set('per_page', perPage);
                url.searchParams.delete('page'); // Reset to first page
                window.location.href = url.toString();
            }
        </script>
    </div>
</x-filament-panels::page>