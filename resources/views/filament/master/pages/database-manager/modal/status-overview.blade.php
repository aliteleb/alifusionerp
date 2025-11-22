{{-- Status Overview Cards Component --}}
<div class="p-6 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-800 dark:to-gray-700">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm border border-gray-200 dark:border-gray-600">
            <div class="flex items-center">
                <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center mr-3">
                    <x-heroicon-o-check-circle class="h-5 w-5 text-green-600 dark:text-green-400"/>
                </div>
                <div>
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400" x-text="ran || 0"></div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('Completed') }}</div>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm border border-gray-200 dark:border-gray-600">
            <div class="flex items-center">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center mr-3" :class="(pending || 0) > 0 ? 'bg-yellow-100 dark:bg-yellow-900' : 'bg-green-100 dark:bg-green-900'">
                    <svg class="h-5 w-5" :class="(pending || 0) > 0 ? 'text-yellow-600 dark:text-yellow-400' : 'text-green-600 dark:text-green-400'" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
                <div>
                    <div class="text-2xl font-bold" :class="(pending || 0) > 0 ? 'text-yellow-600 dark:text-yellow-400' : 'text-green-600 dark:text-green-400'" x-text="pending || 0"></div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('Pending') }}</div>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm border border-gray-200 dark:border-gray-600">
            <div class="flex items-center">
                <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center mr-3">
                    <x-heroicon-o-rectangle-stack class="h-5 w-5 text-blue-600 dark:text-blue-400"/>
                </div>
                <div>
                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400" x-text="total || 0"></div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('Total') }}</div>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm border border-gray-200 dark:border-gray-600">
            <div class="flex items-center">
                <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center mr-3">
                    <x-heroicon-o-chart-pie class="h-5 w-5 text-purple-600 dark:text-purple-400"/>
                </div>
                <div>
                    <div class="text-lg font-bold text-purple-600 dark:text-purple-400" x-text="(total || 0) > 0 ? Math.round(((ran || 0) / (total || 1)) * 100) + '%' : '0%'"></div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('Complete') }}</div>
                </div>
            </div>
            {{-- Progress Bar --}}
            <div class="mt-3 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                <div class="bg-gradient-to-r from-purple-500 to-purple-600 h-2 rounded-full transition-all duration-500 ease-out" 
                     :style="'width: ' + ((total || 0) > 0 ? ((ran || 0) / (total || 1)) * 100 : 0) + '%'"></div>
            </div>
        </div>
    </div>
    
    {{-- Summary Status --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm border border-gray-200 dark:border-gray-600">
        <div class="flex items-center">
            <div class="w-6 h-6 rounded-full mr-3" :class="(pending || 0) > 0 ? 'bg-yellow-400' : 'bg-green-400'"></div>
            <p class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="summary || 'Loading migration status...'"></p>
        </div>
        <div x-show="lastRun" class="mt-2 text-xs text-gray-500 dark:text-gray-400">
            <span>{{ __('Last migration run') }}: </span>
            <span class="font-mono" x-text="lastRun"></span>
        </div>
    </div>
</div>