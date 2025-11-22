<x-filament-widgets::widget>
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ __('Today\'s Tasks') }}
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Your daily action items and priorities') }}
                </p>
            </div>
            <div class="flex items-center space-x-4">
                <div class="text-center">
                    <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('Due Today') }}</div>
                    <div class="text-xl font-bold text-blue-600 dark:text-blue-400">
                        {{ $this->getTaskStats()['due_today_count'] }}
                    </div>
                </div>
                <div class="text-center">
                    <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('Overdue') }}</div>
                    <div class="text-xl font-bold text-red-600 dark:text-red-400">
                        {{ $this->getTaskStats()['overdue_count'] }}
                    </div>
                </div>
                <div class="text-center">
                    <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('Completed') }}</div>
                    <div class="text-xl font-bold text-green-600 dark:text-green-400">
                        {{ $this->getTaskStats()['completed_today'] }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Task Lists --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Overdue Tasks --}}
            <div class="bg-white dark:bg-gray-800 border border-red-200 dark:border-red-800 rounded-lg p-4">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="text-sm font-semibold text-red-600 dark:text-red-400">{{ __('Overdue Tasks') }}</h4>
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                        {{ $this->getTaskStats()['overdue_count'] }}
                    </span>
                </div>
                <div class="space-y-2">
                    @forelse($this->getTodaysTasks()['overdue'] as $task)
                        <div class="flex items-center justify-between p-2 bg-red-50 dark:bg-red-900/20 rounded border border-red-200 dark:border-red-800">
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                    {{ $task->title }}
                                </div>
                                <div class="text-xs text-red-600 dark:text-red-400">
                                    {{ __('Due: ') }}{{ $task->due_date->format('M j') }}
                                </div>
                            </div>
                            <a href="{{ route('filament.admin.resources.tasks.edit', $task) }}"
                               class="ml-2 inline-flex items-center p-1 text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300">
                                <x-heroicon-o-pencil class="h-3 w-3" />
                            </a>
                        </div>
                    @empty
                        <div class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">
                            {{ __('No overdue tasks') }}
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Due Today --}}
            <div class="bg-white dark:bg-gray-800 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="text-sm font-semibold text-blue-600 dark:text-blue-400">{{ __('Due Today') }}</h4>
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                        {{ $this->getTaskStats()['due_today_count'] }}
                    </span>
                </div>
                <div class="space-y-2">
                    @forelse($this->getTodaysTasks()['due_today'] as $task)
                        <div class="flex items-center justify-between p-2 bg-blue-50 dark:bg-blue-900/20 rounded border border-blue-200 dark:border-blue-800">
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                    {{ $task->title }}
                                </div>
                                <div class="text-xs text-blue-600 dark:text-blue-400">
                                    {{ __('Due: ') }}{{ $task->due_date->format('H:i') }}
                                </div>
                            </div>
                            <a href="{{ route('filament.admin.resources.tasks.edit', $task) }}"
                               class="ml-2 inline-flex items-center p-1 text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                                <x-heroicon-o-pencil class="h-3 w-3" />
                            </a>
                        </div>
                    @empty
                        <div class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">
                            {{ __('No tasks due today') }}
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Upcoming --}}
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="text-sm font-semibold text-gray-600 dark:text-gray-400">{{ __('Upcoming (3 days)') }}</h4>
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                        {{ $this->getTodaysTasks()['upcoming']->count() }}
                    </span>
                </div>
                <div class="space-y-2">
                    @forelse($this->getTodaysTasks()['upcoming'] as $task)
                        <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-700 rounded border border-gray-200 dark:border-gray-600">
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                    {{ $task->title }}
                                </div>
                                <div class="text-xs text-gray-600 dark:text-gray-400">
                                    {{ __('Due: ') }}{{ $task->due_date->format('M j') }}
                                </div>
                            </div>
                            <a href="{{ route('filament.admin.resources.tasks.edit', $task) }}"
                               class="ml-2 inline-flex items-center p-1 text-gray-600 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                                <x-heroicon-o-pencil class="h-3 w-3" />
                            </a>
                        </div>
                    @empty
                        <div class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">
                            {{ __('No upcoming tasks') }}
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="flex space-x-3">
            <a href="{{ route('filament.admin.resources.tasks.create') }}"
               class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors">
                <x-heroicon-o-plus class="h-4 w-4 mr-2" />
                {{ __('New Task') }}
            </a>
            <a href="{{ route('filament.admin.resources.tasks.index', ['tableFilters[due_date][value]' => 'today']) }}"
               class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                <x-heroicon-o-list-bullet class="h-4 w-4 mr-2" />
                {{ __('View All Tasks') }}
            </a>
            <a href="{{ route('filament.admin.pages.task-reports') }}"
               class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                <x-heroicon-o-chart-bar class="h-4 w-4 mr-2" />
                {{ __('Task Reports') }}
            </a>
        </div>
    </div>
</x-filament-widgets::widget>
