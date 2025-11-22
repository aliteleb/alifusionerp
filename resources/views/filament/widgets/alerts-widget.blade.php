<x-filament-widgets::widget>
    <div class="space-y-4">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ __('Alerts & Urgent Actions') }}
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Items requiring immediate attention') }}
                </p>
            </div>
            <div class="flex items-center space-x-2">
                <span class="text-xs text-gray-500 dark:text-gray-400">
                    {{ __('Last updated: ') }}{{ now()->format('H:i') }}
                </span>
                <button onclick="refreshAlerts()" 
                        class="inline-flex items-center p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Alerts Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            @foreach($this->getAlerts() as $alert)
                <a href="{{ $alert['url'] }}" class="block">
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-md transition-all duration-200 group cursor-pointer
                         @if($alert['count'] > 0) border-l-4 border-l-{{ $alert['color'] }}-500 @endif">
                        
                        {{-- Icon and Count --}}
                        <div class="flex items-center justify-between mb-2">
                            <div class="w-8 h-8 bg-{{ $alert['color'] }}-100 dark:bg-{{ $alert['color'] }}-900 rounded-lg flex items-center justify-center">
                                <x-filament::icon :icon="$alert['icon']" class="h-4 w-4 text-{{ $alert['color'] }}-600 dark:text-{{ $alert['color'] }}-400" />
                            </div>
                            <div class="text-right">
                                <div class="text-2xl font-bold text-gray-900 dark:text-white
                                    @if($alert['count'] > 0) text-{{ $alert['color'] }}-600 dark:text-{{ $alert['color'] }}-400 @endif">
                                    {{ number_format($alert['count']) }}
                                </div>
                            </div>
                        </div>

                        {{-- Title --}}
                        <div class="text-sm font-medium text-gray-900 dark:text-white group-hover:text-{{ $alert['color'] }}-600 dark:group-hover:text-{{ $alert['color'] }}-400 transition-colors">
                            {{ $alert['title'] }}
                        </div>

                        {{-- Status --}}
                        <div class="mt-2">
                            @if($alert['count'] > 0)
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-{{ $alert['color'] }}-100 text-{{ $alert['color'] }}-800 dark:bg-{{ $alert['color'] }}-900 dark:text-{{ $alert['color'] }}-200">
                                    {{ __('Action Required') }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                    {{ __('All Clear') }}
                                </span>
                            @endif
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        {{-- Quick Actions --}}
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-800 dark:to-gray-700 border border-blue-200 dark:border-gray-600 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                        <x-heroicon-o-cog-6-tooth class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div>
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('Quick Actions') }}</h4>
                        <p class="text-xs text-gray-600 dark:text-gray-400">{{ __('Handle urgent items quickly') }}</p>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('filament.admin.resources.tasks.create') }}"
                       class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 rounded transition-colors">
                        <x-heroicon-o-plus class="h-3 w-3 mr-1" />
                        {{ __('Add Task') }}
                    </a>
                    <a href="{{ route('filament.admin.resources.tickets.create') }}"
                       class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        <x-heroicon-o-lifebuoy class="h-3 w-3 mr-1" />
                        {{ __('New Ticket') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function refreshAlerts() {
            // Refresh the widget data
            window.location.reload();
        }
    </script>
</x-filament-widgets::widget>
