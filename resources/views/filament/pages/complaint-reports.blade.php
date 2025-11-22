<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Header Section --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-xl font-semibold text-gray-900 dark:text-white">
                    {{ __('Complaint Reports') }}
                </h1>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Analyze complaint data and resolution metrics') }}
                </p>
            </div>
            <div class="flex space-x-2 ">
                @php
                    $exportParams = array_filter([
                        'branch_id' => $this->branch_id,
                        'status' => $this->status,
                        'created_from' => $this->created_from,
                        'created_to' => $this->created_to,
                    ]);
                @endphp
                <a href="{{ route('reports.export', array_merge(['type' => 'complaint', 'format' => 'pdf'], $exportParams)) }}" 
                   class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-gray-700">
                    {{ __('PDF') }}
                </a>
                <a href="{{ route('reports.export', array_merge(['type' => 'complaint', 'format' => 'excel'], $exportParams)) }}" 
                   class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-gray-700">
                    {{ __('Excel') }}
                </a>
                <a href="{{ route('reports.export', array_merge(['type' => 'complaint', 'format' => 'csv'], $exportParams)) }}" 
                   class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-gray-700">
                    {{ __('CSV') }}
                </a>
            </div>
        </div>

            {{-- Filters --}}
             <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-800 dark:to-gray-700 border border-blue-200 dark:border-gray-600 rounded-xl p-6 mb-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center space-x-2 ">
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
                                <option value="open">{{ __('Open') }}</option>
                                <option value="in_progress">{{ __('In Progress') }}</option>
                                <option value="resolved">{{ __('Resolved') }}</option>
                                <option value="closed">{{ __('Closed') }}</option>
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

        @php
            $reportData = $this->getReportData();
        @endphp

        {{-- Statistics Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6 shadow-sm">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                        <x-heroicon-o-exclamation-triangle class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div class="ms-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ __('Total Complaints') }}</p>
                        <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($reportData['totalComplaints']) }}</div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6 shadow-sm">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-red-100 dark:bg-red-900 rounded-lg flex items-center justify-center">
                        <x-heroicon-o-exclamation-circle class="h-6 w-6 text-red-600 dark:text-red-400" />
                    </div>
                    <div class="ms-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ __('Open Complaints') }}</p>
                        <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($reportData['openComplaints']) }}</div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6 shadow-sm">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900 rounded-lg flex items-center justify-center">
                        <x-heroicon-o-clock class="h-6 w-6 text-yellow-600 dark:text-yellow-400" />
                    </div>
                    <div class="ms-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ __('In Progress') }}</p>
                        <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($reportData['inProgressComplaints']) }}</div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6 shadow-sm">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                        <x-heroicon-o-check-circle class="h-6 w-6 text-green-600 dark:text-green-400" />
                    </div>
                    <div class="ms-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ __('Resolved') }}</p>
                        <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($reportData['resolvedComplaints']) }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Charts Section --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            {{-- Complaint Status Distribution --}}
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('Complaint Status Distribution') }}</h3>
                <div class="h-64">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>

            {{-- Complaints by Branch --}}
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('Complaints by Branch') }}</h3>
                <div class="h-64">
                    <canvas id="branchChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Monthly Complaint Creation --}}
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6 shadow-sm mb-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('Monthly Complaint Creation') }}</h3>
            <div class="h-64">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>

        {{-- Complaint Data Table --}}
         <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-medium text-gray-900 dark:text-white">{{ __('Complaint Data') }}</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-4 py-3 text-left rtl:text-right rtl:text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Subject') }}</th>
                            <th class="px-4 py-3 text-left rtl:text-right rtl:text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Client') }}</th>
                            <th class="px-4 py-3 text-left rtl:text-right rtl:text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Branch') }}</th>
                            <th class="px-4 py-3 text-left rtl:text-right rtl:text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800">
                        @forelse($reportData['complaints'] as $complaint)
                            <tr class="even:bg-gray-50 dark:even:bg-gray-700">
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white text-left rtl:text-right rtl:text-right">{{ $complaint->subject }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300 text-left rtl:text-right rtl:text-right">
                                    @if($complaint->client)
                                        {{ $complaint->client->name }}
                                    @else
                                        <span class="inline-flex px-2 py-1 text-xs font-medium text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 rounded">
                                            {{ __('No Client') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300 text-left rtl:text-right rtl:text-right">
                                    @if($complaint->branch)
                                        {{ $complaint->branch->name }}
                                    @else
                                        <span class="inline-flex px-2 py-1 text-xs font-medium text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 rounded">
                                            {{ __('No Branch') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-left rtl:text-right rtl:text-right">
                                    @if($complaint->status)
                                        @switch($complaint->status->value)
                                            @case('open')
                                                <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                                    {{ __('Open') }}
                                              </span>
                                                @break
                                            @case('in_progress')
                                                <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                                    {{ __('In Progress') }}
                                              </span>
                                                @break
                                            @case('resolved')
                                                <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                    {{ __('Resolved') }}
                                              </span>
                                                @break
                                            @case('closed')
                                                <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">
                                                    {{ __('Closed') }}
                                              </span>
                                                @break
                                            @default
                                                <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">
                                                    {{ $complaint->status->getLabel() }}
                                                </span>
                                        @endswitch
                                    @else
                                        <span class="inline-flex px-2 py-1 text-xs font-medium text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 rounded-full">
                                            {{ __('No Status') }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                    {{ __('No complaints found matching the criteria.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if(method_exists($reportData['complaints'], 'hasPages') && $reportData['complaints']->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4 rtl:space-x-reverse">
                            @if(method_exists($reportData['complaints'], 'currentPage'))
                                <span class="text-sm text-gray-700 dark:text-gray-300">
                                    {{ __('Showing :first to :last of :total results', [
                                        'first' => $reportData['complaints']->firstItem(),
                                        'last' => $reportData['complaints']->lastItem(),
                                        'total' => $reportData['complaints']->total()
                                    ]) }}
                                </span>
                            @else
                                <span class="text-sm text-gray-700 dark:text-gray-300">
                                    {{ __('Showing :count results', ['count' => $reportData['complaints']->count()]) }}
                                </span>
                            @endif
                        </div>

                        <div class="flex items-center space-x-2 rtl:space-x-reverse">
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Per page:') }}</span>
                            <select wire:model="perPage" class="text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded">
                                <option value="10" {{ $this->perPage == 10 ? 'selected' : '' }}>10</option>
                                <option value="25" {{ $this->perPage == 25 ? 'selected' : '' }}>25</option>
                                <option value="50" {{ $this->perPage == 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ $this->perPage == 100 ? 'selected' : '' }}>100</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center justify-between mt-4">
                        <div>
                            @if(method_exists($reportData['complaints'], 'currentPage') && $reportData['complaints']->currentPage() > 1)
                                <a href="{{ $reportData['complaints']->previousPageUrl() }}" class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <x-heroicon-o-chevron-left class="h-4 w-4 rtl:rotate-180" />
                                    {{ __('Previous') }}
                                </a>
                            @else
                                <span class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-300 dark:text-gray-600 bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md cursor-not-allowed">
                                    <x-heroicon-o-chevron-left class="h-4 w-4 rtl:rotate-180" />
                                    {{ __('Previous') }}
                                </span>
                            @endif
                        </div>

                        <div class="flex space-x-1 rtl:space-x-reverse">
                            @if(method_exists($reportData['complaints'], 'getUrlRange'))
                                @foreach($reportData['complaints']->getUrlRange(1, $reportData['complaints']->lastPage()) as $page => $url)
                                    @if($page == $reportData['complaints']->currentPage())
                                        <span class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-blue-600 border border-blue-600 rounded-md">
                                            {{ $page }}
                                        </span>
                                    @else
                                        <a href="{{ $url }}" class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700">
                                            {{ $page }}
                                        </a>
                                    @endif
                                @endforeach
                            @endif
                        </div>

                        <div>
                            @if(method_exists($reportData['complaints'], 'currentPage') && $reportData['complaints']->hasMorePages())
                                <a href="{{ $reportData['complaints']->nextPageUrl() }}" class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700">
                                    {{ __('Next') }}
                                    <x-heroicon-o-chevron-right class="h-4 w-4 rtl:rotate-180" />
                                </a>
                            @else
                                <span class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-300 dark:text-gray-600 bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md cursor-not-allowed">
                                    {{ __('Next') }}
                                    <x-heroicon-o-chevron-right class="h-4 w-4 rtl:rotate-180" />
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <script>
            // Chart data from PHP
            // Initial chart data
            window.complaintChartData = @json($this->chartData);

            // Chart instances storage - using window scope to avoid conflicts
            window.complaintStatusChart = null;
            window.complaintBranchChart = null;
            window.complaintMonthlyChart = null;

            // Function to create charts
            function createCharts(chartData = null) {
                // Use provided chart data or keep existing data
                if (chartData) {
                    window.complaintChartData = chartData;
                }
                
                // Destroy existing charts if they exist
                if (window.complaintStatusChart) window.complaintStatusChart.destroy();
                if (window.complaintBranchChart) window.complaintBranchChart.destroy();
                if (window.complaintMonthlyChart) window.complaintMonthlyChart.destroy();

                // Status Chart
                const statusCanvas = document.getElementById('statusChart');
                if (!statusCanvas) return; // Exit if canvas not found
                
                const statusCtx = statusCanvas.getContext('2d');
                window.complaintStatusChart = new Chart(statusCtx, {
                    type: 'doughnut',
                    data: {
                        labels: Object.keys(window.complaintChartData.status || {}),
                        datasets: [{
                            data: Object.values(window.complaintChartData.status || {}),
                            backgroundColor: [
                                '#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6'
                            ],
                            borderWidth: 0
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

                // Branch Chart
                const branchCanvas = document.getElementById('branchChart');
                if (!branchCanvas) return; // Exit if canvas not found
                
                const branchCtx = branchCanvas.getContext('2d');
                window.complaintBranchChart = new Chart(branchCtx, {
                    type: 'bar',
                    data: {
                        labels: Object.keys(window.complaintChartData.branches || {}),
                        datasets: [{
                            label: '{{ __('Complaints') }}',
                            data: Object.values(window.complaintChartData.branches || {}),
                            backgroundColor: '#3B82F6',
                            borderRadius: 4
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
                                    precision: 0
                                }
                            }
                        }
                    }
                });

                // Monthly Chart
                const monthlyCanvas = document.getElementById('monthlyChart');
                if (!monthlyCanvas) return; // Exit if canvas not found
                
                const monthlyCtx = monthlyCanvas.getContext('2d');
                window.complaintMonthlyChart = new Chart(monthlyCtx, {
                    type: 'line',
                    data: {
                        labels: window.complaintChartData.monthly ? window.complaintChartData.monthly.map(item => item.month) : [],
                        datasets: [{
                            label: '{{ __('New Complaints') }}',
                            data: window.complaintChartData.monthly ? window.complaintChartData.monthly.map(item => item.count) : [],
                            borderColor: '#3B82F6',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            borderWidth: 2,
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
                                    precision: 0
                                }
                            }
                        }
                    }
                });
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
    </div>
</x-filament-panels::page>