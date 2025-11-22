<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Header Section --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-xl font-semibold text-gray-900 dark:text-white">
                    {{ __('Announcement Reports') }}
                </h1>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Analyze announcement data and performance') }}
                </p>
            </div>
            <div class="flex space-x-2">
                @php
                    $exportParams = array_filter([
                        'status' => $this->status,
                        'created_from' => $this->created_from,
                        'created_to' => $this->created_to,
                    ]);
                @endphp
                <a href="{{ route('reports.export', array_merge(['type' => 'announcement', 'format' => 'pdf'], $exportParams)) }}" 
                   class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-gray-700">
                    {{ __('PDF') }}
                </a>
                <a href="{{ route('reports.export', array_merge(['type' => 'announcement', 'format' => 'excel'], $exportParams)) }}" 
                   class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-gray-700">
                    {{ __('Excel') }}
                </a>
                <a href="{{ route('reports.export', array_merge(['type' => 'announcement', 'format' => 'csv'], $exportParams)) }}" 
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
            
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
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
                                <option value="inactive">{{ __('Inactive') }}</option>
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
                <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('Total Announcements') }}</div>
                <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($reportData['total_announcements']) }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded p-4">
                <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('Active Announcements') }}</div>
                <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($reportData['active_announcements']) }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded p-4">
                <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('Inactive Announcements') }}</div>
                <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($reportData['inactive_announcements']) }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded p-4">
                <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('Monthly Average') }}</div>
                <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($reportData['monthly_registrations']->avg('count'), 1) }}</div>
            </div>
        </div>

        {{-- Charts Section --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            {{-- Announcement Status Chart --}}
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-white">{{ __('Announcement Status Distribution') }}</h2>
                </div>
                <div class="p-6">
                    <canvas id="statusChart" width="400" height="200"></canvas>
                </div>
            </div>

            {{-- Monthly Creation Chart --}}
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-white">{{ __('Monthly Announcement Creation') }}</h2>
                </div>
                <div class="p-6">
                    <canvas id="monthlyChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>

        {{-- Announcement Data Table --}}
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-medium text-gray-900 dark:text-white">{{ __('Announcement Data') }}</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-4 py-3 text-left rtl:text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Title') }}</th>
                            <th class="px-4 py-3 text-left rtl:text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Status') }}</th>
                            <th class="px-4 py-3 text-left rtl:text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Created By') }}</th>
                            <th class="px-4 py-3 text-left rtl:text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Created At') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800">
                        @forelse($reportData['announcements'] as $announcement)
                            <tr class="even:bg-gray-50 dark:even:bg-gray-700">
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white text-left rtl:text-right">{{ $announcement->title }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300 text-left rtl:text-right">
                                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full {{ $announcement->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                        {{ $announcement->is_active ? __('Active') : __('Inactive') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300 text-left rtl:text-right">
                                    <span class="inline-flex px-2 py-1 text-xs font-medium text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 rounded">
                                        {{ __('N/A') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300 text-left rtl:text-right">{{ $announcement->created_at ? $announcement->created_at->format('Y-m-d H:i') : __('N/A') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                    {{ __('No announcements found matching the criteria.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{-- Pagination --}}
            @if(method_exists($reportData['announcements'], 'hasPages') && $reportData['announcements']->hasPages())
                <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="text-sm text-gray-700 dark:text-gray-300">
                                @if(method_exists($reportData['announcements'], 'firstItem'))
                                    {{ __('Showing :first to :last of :total results', [
                                        'first' => $reportData['announcements']->firstItem(),
                                        'last' => $reportData['announcements']->lastItem(),
                                        'total' => number_format($reportData['announcements']->total())
                                    ]) }}
                                @else
                                    {{ __('Showing :count results', ['count' => $reportData['announcements']->count()]) }}
                                @endif
                            </div>
                            
                            {{-- Per Page Selector --}}
                            <div class="flex items-center space-x-2">
                                <label class="text-sm text-gray-700 dark:text-gray-300">{{ __('Per page:') }}</label>
                                <x-filament::input.wrapper>
                                    <x-filament::input.select wire:model="perPage" class="text-sm">
                                        <option value="10">10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </x-filament::input.select>
                                </x-filament::input.wrapper>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            {{-- Previous Page --}}
                            @if(method_exists($reportData['announcements'], 'onFirstPage') && !$reportData['announcements']->onFirstPage())
                                <a href="{{ $reportData['announcements']->previousPageUrl() }}" 
                                   class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <x-heroicon-o-chevron-left class="h-4 w-4 me-1 rtl:rotate-180" />
                                    {{ __('Previous') }}
                                </a>
                            @else
                                <span class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-gray-400 bg-gray-100 dark:bg-gray-700 rounded cursor-not-allowed">
                                    <x-heroicon-o-chevron-left class="h-4 w-4 me-1 rtl:rotate-180" />
                                    {{ __('Previous') }}
                                </span>
                            @endif

                            {{-- Page Numbers --}}
                            @if(method_exists($reportData['announcements'], 'currentPage'))
                                @php
                                    $currentPage = $reportData['announcements']->currentPage();
                                    $lastPage = $reportData['announcements']->lastPage();
                                    $startPage = max(1, $currentPage - 2);
                                    $endPage = min($lastPage, $currentPage + 2);
                                @endphp
                                
                                {{-- Show first page if not in range --}}
                                @if($startPage > 1)
                                    <a href="{{ $reportData['announcements']->url(1) }}" 
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
                                        <a href="{{ $reportData['announcements']->url($page) }}" 
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
                                        <a href="{{ $reportData['announcements']->url($lastPage) }}" 
                                           class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-gray-700">
                                        {{ $lastPage }}
                                    </a>
                                @endif
                                @endif

                            {{-- Next Page --}}
                            @if(method_exists($reportData['announcements'], 'hasMorePages') && $reportData['announcements']->hasMorePages())
                                <a href="{{ $reportData['announcements']->nextPageUrl() }}" 
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
                        </div>
                    </div>
                </div>
            @endif
    </div>

    <script>
            // Chart data from computed property
            window.announcementChartData = @json($this->chartData);

            // Chart instances storage - using window scope to avoid conflicts
            window.announcementStatusChart = null;
            window.announcementMonthlyChart = null;

            // Function to create charts
            function createCharts(chartData = null) {
                // Use provided chartData or fallback to window data
                const data = chartData || window.announcementChartData;
                // Destroy existing charts if they exist
                if (window.announcementStatusChart) window.announcementStatusChart.destroy();
                if (window.announcementMonthlyChart) window.announcementMonthlyChart.destroy();

                // Status Chart (Doughnut)
                const statusCanvas = document.getElementById('statusChart');
                if (!statusCanvas) return; // Exit if canvas not found
                
                const statusCtx = statusCanvas.getContext('2d');
                window.announcementStatusChart = new Chart(statusCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['{{ __("Active") }}', '{{ __("Inactive") }}'],
                        datasets: [{
                            data: [data.status.active, data.status.inactive],
                            backgroundColor: ['#10B981', '#EF4444'],
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

                // Monthly Creation Chart (Line)
                const monthlyCanvas = document.getElementById('monthlyChart');
                if (!monthlyCanvas) return; // Exit if canvas not found
                
                const monthlyCtx = monthlyCanvas.getContext('2d');
                
                const monthlyLabels = data.monthly && data.monthly.length > 0 ? 
                    data.monthly.map(item => item.month) : 
                    ['{{ __("No Data") }}'];
                const monthlyValues = data.monthly && data.monthly.length > 0 ? 
                    data.monthly.map(item => item.count) : 
                    [0];
                
                window.announcementMonthlyChart = new Chart(monthlyCtx, {
                    type: 'line',
                    data: {
                        labels: monthlyLabels,
                        datasets: [{
                            label: '{{ __("New Announcements") }}',
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
