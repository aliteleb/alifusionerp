<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Header Section --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-xl font-semibold text-gray-900 dark:text-white">
                    {{ __('Opportunity Reports') }}
                </h1>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Analyze sales opportunities and revenue') }}
                </p>
            </div>
            <div class="flex space-x-2">
                @php
                    $exportParams = array_filter([
                        'branch_id' => $this->branch_id,
                        'state' => $this->state,
                        'created_from' => $this->created_from,
                        'created_to' => $this->created_to,
                    ]);
                @endphp
                <a href="{{ route('reports.export', array_merge(['type' => 'opportunity', 'format' => 'pdf'], $exportParams)) }}" 
                   class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-gray-700">
                    {{ __('PDF') }}
                </a>
                <a href="{{ route('reports.export', array_merge(['type' => 'opportunity', 'format' => 'excel'], $exportParams)) }}" 
                   class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-gray-700">
                    {{ __('Excel') }}
                </a>
                <a href="{{ route('reports.export', array_merge(['type' => 'opportunity', 'format' => 'csv'], $exportParams)) }}" 
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

                    {{-- State Filter --}}
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            <div class="flex items-center space-x-1">
                                <x-heroicon-o-check-circle class="h-4 w-4" />
                                <span>{{ __('State') }}</span>
                            </div>
                        </label>
            <x-filament::input.wrapper>
                <x-filament::input.select wire:model="state" placeholder="{{ __('All States') }}">
                    <option value="open">{{ __('Open') }}</option>
                    <option value="won">{{ __('Won') }}</option>
                    <option value="lost">{{ __('Lost') }}</option>
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
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded p-4">
                <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('Total Opportunities') }}</div>
                <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($reportData['total_opportunities']) }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded p-4">
                <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('Open Opportunities') }}</div>
                <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($reportData['open_opportunities']) }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded p-4">
                <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('Won Opportunities') }}</div>
                <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($reportData['won_opportunities']) }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded p-4">
                <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('Total Revenue') }}</div>
                <div class="text-2xl font-semibold text-gray-900 dark:text-white">${{ number_format($reportData['total_revenue'], 2) }}</div>
            </div>
        </div>

        {{-- Charts Section --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            {{-- Opportunity State Chart --}}
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Opportunity State Distribution') }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Overview of opportunity states') }}</p>
                </div>
                <div class="p-6">
                    <div class="h-64">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>

            {{-- Monthly Opportunity Creation Chart --}}
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Monthly Opportunity Creation') }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Opportunities created over time') }}</p>
                </div>
                <div class="p-6">
                    <div class="h-64">
                        <canvas id="monthlyChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Opportunity Data Table --}}
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Opportunity Data') }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Detailed opportunity information') }}</p>
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        {{ __('Showing') }} {{ $reportData['opportunities']->firstItem() ?? 0 }} {{ __('to') }} {{ $reportData['opportunities']->lastItem() ?? 0 }} {{ __('of') }} {{ $reportData['opportunities']->total() }} {{ __('results') }}
                    </div>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-4 py-3 text-left rtl:text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Name') }}</th>
                            <th class="px-4 py-3 text-left rtl:text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Client') }}</th>
                            <th class="px-4 py-3 text-left rtl:text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Branch') }}</th>
                            <th class="px-4 py-3 text-left rtl:text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Assigned To') }}</th>
                            <th class="px-4 py-3 text-left rtl:text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('State') }}</th>
                            <th class="px-4 py-3 text-left rtl:text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Expected Revenue') }}</th>
                            <th class="px-4 py-3 text-left rtl:text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Created At') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800">
                        @forelse($reportData['opportunities'] as $opportunity)
                            <tr class="even:bg-gray-50 dark:even:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center me-3">
                                            <x-heroicon-o-currency-dollar class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                                        </div>
                                        <div>
                                            <div class="font-medium">{{ $opportunity->name }}</div>
                                            @if($opportunity->description)
                                                <div class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-xs">
                                                    {{ Str::limit($opportunity->description, 50) }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                                    @if($opportunity->client)
                                        {{ $opportunity->client->name }}
                                    @else
                                        <span class="inline-flex px-2 py-1 text-xs font-medium text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 rounded">
                                            {{ __('No Client') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                                    @if($opportunity->branch)
                                        {{ $opportunity->branch->name }}
                                    @else
                                        <span class="inline-flex px-2 py-1 text-xs font-medium text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 rounded">
                                            {{ __('No Branch') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                                    @if($opportunity->assignedTo)
                                        {{ $opportunity->assignedTo->name }}
                                    @else
                                        <span class="inline-flex px-2 py-1 text-xs font-medium text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 rounded">
                                            {{ __('Unassigned') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @php
                                        $stateColors = [
                                            'open' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                            'won' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                            'lost' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                        ];
                                        $stateValue = $opportunity->state->value;
                                        $stateColor = $stateColors[$stateValue] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200';
                                    @endphp
                                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full {{ $stateColor }}">
                                        {{ $opportunity->state->getLabel() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                                    @if($opportunity->expected_revenue)
                                        <div class="flex items-center">
                                            <x-heroicon-o-currency-dollar class="h-4 w-4 text-gray-400 me-1" />
                                            {{ number_format($opportunity->expected_revenue, 2) }}
                                        </div>
                                    @else
                                        <span class="inline-flex px-2 py-1 text-xs font-medium text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 rounded">
                                            {{ __('No Revenue') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $opportunity->created_at->format('Y-m-d H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                    {{ __('No opportunities found matching your criteria.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($reportData['opportunities']->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="text-sm text-gray-700 dark:text-gray-300">
                                {{ __('Showing') }} {{ $reportData['opportunities']->firstItem() }} {{ __('to') }} {{ $reportData['opportunities']->lastItem() }} {{ __('of') }} {{ $reportData['opportunities']->total() }} {{ __('results') }}
                            </div>
                            
                            {{-- Per Page Selector --}}
                            <div class="flex items-center space-x-2 ">
                                <label class="text-sm text-gray-700 dark:text-gray-300">{{ __('Per page:') }}</label>
                                <x-filament::input.wrapper>
                                    <x-filament::input.select wire:model="perPage" class="text-sm">
                                        <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                                    </x-filament::input.select>
                                </x-filament::input.wrapper>
                            </div>
                        </div>
                        
                        <div class="flex items-center space-x-2">
                            {{-- Previous Button --}}
                            @if($reportData['opportunities']->onFirstPage())
                                <span class="px-3 py-1 text-sm text-gray-400 bg-gray-100 dark:bg-gray-700 rounded cursor-not-allowed">
                                    {{ __('Previous') }}
                                </span>
                            @else
                                <a href="{{ $reportData['opportunities']->previousPageUrl() }}" class="px-3 py-1 text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-gray-700">
                                    {{ __('Previous') }}
                                </a>
                            @endif

                            {{-- Page Numbers --}}
                            @php
                                $currentPage = $reportData['opportunities']->currentPage();
                                $lastPage = $reportData['opportunities']->lastPage();
                                $startPage = max(1, $currentPage - 2);
                                $endPage = min($lastPage, $currentPage + 2);
                            @endphp

                            @if($startPage > 1)
                                <a href="{{ $reportData['opportunities']->url(1) }}" class="px-3 py-1 text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-gray-700">1</a>
                                @if($startPage > 2)
                                    <span class="px-2 text-gray-400">...</span>
                                @endif
                            @endif

                            @for($page = $startPage; $page <= $endPage; $page++)
                                @if($page == $currentPage)
                                    <span class="px-3 py-1 text-sm text-white bg-blue-600 rounded">{{ $page }}</span>
                                @else
                                    <a href="{{ $reportData['opportunities']->url($page) }}" class="px-3 py-1 text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-gray-700">{{ $page }}</a>
                                @endif
                            @endfor

                            @if($endPage < $lastPage)
                                @if($endPage < $lastPage - 1)
                                    <span class="px-2 text-gray-400">...</span>
                                @endif
                                <a href="{{ $reportData['opportunities']->url($lastPage) }}" class="px-3 py-1 text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-gray-700">{{ $lastPage }}</a>
                            @endif

                            {{-- Next Button --}}
                            @if($reportData['opportunities']->hasMorePages())
                                <a href="{{ $reportData['opportunities']->nextPageUrl() }}" class="px-3 py-1 text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-gray-700">
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
        // Chart data from Livewire component
        window.opportunityChartData = @json($this->getChartData());

        // Chart instances storage
        let statusChart = null;
        let monthlyChart = null;

        // Function to create charts
        async function createCharts(chartData = null) {
            // Use provided chart data or keep existing data
            if (chartData) {
                window.opportunityChartData = chartData;
            }
            
            // Destroy existing charts if they exist
            if (statusChart) statusChart.destroy();
            if (monthlyChart) monthlyChart.destroy();

            // Status Chart (Doughnut)
            const statusCanvas = document.getElementById('statusChart');
            if (!statusCanvas) return; // Exit if canvas not found
            
            const statusCtx = statusCanvas.getContext('2d');
            statusChart = new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: [
                        '{{ __("Open") }}', 
                        '{{ __("Won") }}', 
                        '{{ __("Lost") }}'
                    ],
                    datasets: [{
                        data: [
                            window.opportunityChartData.status.open, 
                            window.opportunityChartData.status.won, 
                            window.opportunityChartData.status.lost
                        ],
                        backgroundColor: [
                            '#3B82F6', // Blue for open
                            '#10B981', // Green for won
                            '#EF4444'  // Red for lost
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

            // Monthly Opportunity Creation Chart (Line)
            const monthlyCanvas = document.getElementById('monthlyChart');
            if (!monthlyCanvas) return; // Exit if canvas not found
            
            const monthlyCtx = monthlyCanvas.getContext('2d');
            
            const monthlyLabels = window.opportunityChartData.monthly && window.opportunityChartData.monthly.length > 0 ? 
                window.opportunityChartData.monthly.map(item => item.month) : 
                ['{{ __("No Data") }}'];
            const monthlyValues = window.opportunityChartData.monthly && window.opportunityChartData.monthly.length > 0 ? 
                window.opportunityChartData.monthly.map(item => item.count) : 
                [0];
            
            monthlyChart = new Chart(monthlyCtx, {
                type: 'line',
                data: {
                    labels: monthlyLabels,
                    datasets: [{
                        label: '{{ __("New Opportunities") }}',
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


        // Initialize charts when page loads
        document.addEventListener('DOMContentLoaded', async function() {
            await createCharts();
        });

        // Recreate charts when Livewire updates (for SPA navigation)
        document.addEventListener('livewire:navigated', async function() {
            await createCharts();
        });

        // Recreate charts when filters are applied - improved event handling
        document.addEventListener('livewire:init', () => {
            Livewire.on('recreate-charts', async (event) => {
                console.log('Chart recreation event received:', event);
                await createCharts(event.chartData);
            });
        });

        // Additional event listener for Livewire updates
        document.addEventListener('livewire:updated', async function() {
            // Small delay to ensure DOM is updated
            setTimeout(async () => {
                await createCharts();
            }, 100);
        });

        // Listen for Livewire component updates specifically
        document.addEventListener('livewire:updated', async function(event) {
            // Check if this is a component update that might affect charts
            if (event.detail && event.detail.component) {
                setTimeout(async () => {
                    await createCharts();
                }, 150);
            }
        });
    </script>
</x-filament-panels::page>
