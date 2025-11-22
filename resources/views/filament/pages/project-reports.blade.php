<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Header Section --}}
        <div class="flex items-center justify-between mb-6">
                    <div>
                <h1 class="text-xl font-semibold text-gray-900 dark:text-white">
                               {{ __('Project Reports') }}
                           </h1>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Analyze project data and progress') }}
                           </p>
                </div>
                <div class="flex space-x-2">
                @php
                    $exportParams = array_filter([
                        'branch_id' => $this->branch_id,
                        'status' => $this->status,
                        'created_from' => $this->created_from,
                        'created_to' => $this->created_to,
                    ]);
                @endphp
                <a href="{{ route('reports.export', array_merge(['type' => 'project', 'format' => 'pdf'], $exportParams)) }}" 
                   class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-gray-700">
                    {{ __('PDF') }}
                </a>
                <a href="{{ route('reports.export', array_merge(['type' => 'project', 'format' => 'excel'], $exportParams)) }}" 
                   class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-gray-700">
                    {{ __('Excel') }}
                </a>
                <a href="{{ route('reports.export', array_merge(['type' => 'project', 'format' => 'csv'], $exportParams)) }}" 
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
                                <option value="not_started">{{ __('Not Started') }}</option>
                                <option value="started">{{ __('Started') }}</option>
                                <option value="in_progress">{{ __('In Progress') }}</option>
                                <option value="on_hold">{{ __('On Hold') }}</option>
                                <option value="overdue">{{ __('Overdue') }}</option>
                                <option value="cancelled">{{ __('Cancelled') }}</option>
                                <option value="completed">{{ __('Completed') }}</option>
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
                <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('Total Projects') }}</div>
                <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($reportData['total_projects']) }}</div>
                        </div>
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded p-4">
                <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('Not Started') }}</div>
                <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($reportData['not_started_projects']) }}</div>
                    </div>
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded p-4">
                <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('In Progress') }}</div>
                <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($reportData['in_progress_projects']) }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded p-4">
                <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('Completed') }}</div>
                <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($reportData['completed_projects']) }}</div>
                        </div>
                    </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded p-4">
                <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('Started') }}</div>
                <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($reportData['started_projects']) }}</div>
                    </div>
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded p-4">
                <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('On Hold') }}</div>
                <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($reportData['on_hold_projects']) }}</div>
                </div>
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded p-4">
                <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('Overdue') }}</div>
                <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($reportData['overdue_projects']) }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded p-4">
                <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('Cancelled') }}</div>
                <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($reportData['cancelled_projects']) }}</div>
                        </div>
                    </div>

        {{-- Charts Section --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            {{-- Project Status Chart --}}
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded">
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-white">{{ __('Project Status Distribution') }}</h2>
                    </div>
                <div class="p-4">
                    <canvas id="statusChart" width="400" height="200"></canvas>
                </div>
            </div>

            {{-- Branch Distribution Chart --}}
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded">
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-white">{{ __('Projects by Branch') }}</h2>
                        </div>
                <div class="p-4">
                    <canvas id="branchChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>

        {{-- Monthly Registration Chart --}}
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded mb-6">
            <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-medium text-gray-900 dark:text-white">{{ __('Monthly Project Creation') }}</h2>
            </div>
            <div class="p-4">
                <canvas id="monthlyChart" width="800" height="300"></canvas>
            </div>
        </div>

        {{-- Project Data Table --}}
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded">
            <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-medium text-gray-900 dark:text-white">{{ __('Project Data') }}</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-4 py-3 text-left rtl:text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Name') }}</th>
                            <th class="px-4 py-3 text-left rtl:text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Client') }}</th>
                            <th class="px-4 py-3 text-left rtl:text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Created By') }}</th>
                            <th class="px-4 py-3 text-left rtl:text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Branch') }}</th>
                            <th class="px-4 py-3 text-left rtl:text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Status') }}</th>
                            <th class="px-4 py-3 text-left rtl:text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Created At') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800">
                        @forelse($reportData['projects'] as $project)
                            <tr class="even:bg-gray-50 dark:even:bg-gray-700">
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white text-left rtl:text-right">{{ $project->name }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300 text-left rtl:text-right">
                                    @if($project->client)
                                        {{ $project->client->name }}
                                    @else
                                        <span class="inline-flex px-2 py-1 text-xs font-medium text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 rounded">
                                            {{ __('No Client') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300 text-left rtl:text-right">
                                    <span class="inline-flex px-2 py-1 text-xs font-medium text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 rounded">
                                        {{ __('N/A') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300 text-left rtl:text-right">
                                    @if($project->branch)
                                        {{ $project->branch->name }}
                                    @else
                                        <span class="inline-flex px-2 py-1 text-xs font-medium text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 rounded">
                                            {{ __('No Branch') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-left rtl:text-right">
                                    @php
                                        $statusColors = [
                                            'not_started' => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
                                            'started' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                            'in_progress' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                            'on_hold' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
                                            'overdue' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                            'cancelled' => 'bg-slate-100 text-slate-800 dark:bg-slate-900 dark:text-slate-200',
                                            'completed' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                        ];
                                        $statusValue = $project->status->value;
                                        $statusColor = $statusColors[$statusValue] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200';
                                    @endphp
                                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full {{ $statusColor }}">
                                        {{ $project->status->getLabel() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300 text-left rtl:text-right">{{ $project->created_at->format('Y-m-d H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                            {{ __('No projects found matching the criteria.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{-- Pagination --}}
            @if($reportData['projects']->hasPages())
                <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4 rtl:space-x-reverse">
                            <div class="text-sm text-gray-700 dark:text-gray-300">
                                {{ __('Showing :first to :last of :total results', [
                                    'first' => $reportData['projects']->firstItem(),
                                    'last' => $reportData['projects']->lastItem(),
                                    'total' => number_format($reportData['projects']->total())
                                ]) }}
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
                            {{-- Previous Page --}}
                            @if($reportData['projects']->onFirstPage())
                                <span class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-gray-400 bg-gray-100 dark:bg-gray-700 rounded cursor-not-allowed">
                                    <x-heroicon-o-chevron-left class="h-4 w-4 me-1 rtl:rotate-180" />
                                    {{ __('Previous') }}
                                </span>
                            @else
                                <a href="{{ $reportData['projects']->previousPageUrl() }}" 
                                   class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <x-heroicon-o-chevron-left class="h-4 w-4 me-1 rtl:rotate-180" />
                                    {{ __('Previous') }}
                                </a>
                            @endif

                            {{-- Page Numbers --}}
                            @php
                                $currentPage = $reportData['projects']->currentPage();
                                $lastPage = $reportData['projects']->lastPage();
                                $startPage = max(1, $currentPage - 2);
                                $endPage = min($lastPage, $currentPage + 2);
                            @endphp
                            
                            {{-- Show first page if not in range --}}
                            @if($startPage > 1)
                                <a href="{{ $reportData['projects']->url(1) }}" 
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
                                    <a href="{{ $reportData['projects']->url($page) }}" 
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
                                <a href="{{ $reportData['projects']->url($lastPage) }}" 
                                   class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-gray-700">
                                    {{ $lastPage }}
                                </a>
                            @endif

                            {{-- Next Page --}}
                            @if($reportData['projects']->hasMorePages())
                                <a href="{{ $reportData['projects']->nextPageUrl() }}" 
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

    </div>

    <script>
        // Chart data from PHP
        // Initial chart data
        window.projectChartData = @json($this->chartData);

        // Chart instances storage - using window scope to avoid conflicts
        window.projectStatusChart = null;
        window.projectBranchChart = null;
        window.projectMonthlyChart = null;

        // Function to create charts
        function createCharts(chartData = null) {
            // Use provided chart data or keep existing data
            if (chartData) {
                window.projectChartData = chartData;
            }
            
            // Destroy existing charts if they exist
            if (window.projectStatusChart) window.projectStatusChart.destroy();
            if (window.projectBranchChart) window.projectBranchChart.destroy();
            if (window.projectMonthlyChart) window.projectMonthlyChart.destroy();

            // Status Chart (Doughnut)
            const statusCanvas = document.getElementById('statusChart');
            if (!statusCanvas) return; // Exit if canvas not found
            
            const statusCtx = statusCanvas.getContext('2d');
            window.projectStatusChart = new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: [
                        '{{ __("Not Started") }}', 
                        '{{ __("Started") }}', 
                        '{{ __("In Progress") }}', 
                        '{{ __("On Hold") }}', 
                        '{{ __("Overdue") }}', 
                        '{{ __("Cancelled") }}', 
                        '{{ __("Completed") }}'
                    ],
                    datasets: [{
                        data: [
                            window.projectChartData.status.not_started, 
                            window.projectChartData.status.started, 
                            window.projectChartData.status.in_progress, 
                            window.projectChartData.status.on_hold, 
                            window.projectChartData.status.overdue, 
                            window.projectChartData.status.cancelled, 
                            window.projectChartData.status.completed
                        ],
                        backgroundColor: [
                            '#6B7280', // Gray for not started
                            '#3B82F6', // Blue for started
                            '#F59E0B', // Yellow for in progress
                            '#F97316', // Orange for on hold
                            '#EF4444', // Red for overdue
                            '#64748B', // Slate for cancelled
                            '#10B981'  // Green for completed
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
                                usePointStyle: true
                            }
                        }
                    }
                }
            });

            // Branch Chart (Bar)
            const branchCanvas = document.getElementById('branchChart');
            if (!branchCanvas) return; // Exit if canvas not found
            
            const branchCtx = branchCanvas.getContext('2d');
            const branchLabels = Object.keys(window.projectChartData.branches);
            const branchValues = Object.values(window.projectChartData.branches);
            
            window.projectBranchChart = new Chart(branchCtx, {
                type: 'bar',
                data: {
                    labels: branchLabels.map(label => label || '{{ __("N/A") }}'),
                    datasets: [{
                        label: '{{ __("Projects") }}',
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

            // Monthly Registration Chart (Line)
            const monthlyCanvas = document.getElementById('monthlyChart');
            if (!monthlyCanvas) return; // Exit if canvas not found
            
            const monthlyCtx = monthlyCanvas.getContext('2d');
            
            const monthlyLabels = window.projectChartData.monthly && window.projectChartData.monthly.length > 0 ? 
                window.projectChartData.monthly.map(item => item.month) : 
                ['{{ __("No Data") }}'];
            const monthlyValues = window.projectChartData.monthly && window.projectChartData.monthly.length > 0 ? 
                window.projectChartData.monthly.map(item => item.count) : 
                [0];
            
            window.projectMonthlyChart = new Chart(monthlyCtx, {
                type: 'line',
                data: {
                    labels: monthlyLabels,
                    datasets: [{
                        label: '{{ __("New Projects") }}',
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
</x-filament-panels::page>
