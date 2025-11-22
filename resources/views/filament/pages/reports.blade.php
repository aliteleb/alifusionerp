<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Header Section --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center space-x-3 mb-4">
                <div class="flex-shrink-0">
                    <x-heroicon-o-chart-bar class="h-8 w-8 text-primary-600 dark:text-primary-400" />
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ __('Reports Dashboard') }}
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400">
                        {{ __('Generate comprehensive reports for your business operations') }}
                    </p>
                </div>
            </div>
        </div>


        {{-- Organization Reports Section --}}
        <div class="mb-8">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">{{ __('Organization Reports') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                {{-- Branch Reports --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                                <x-heroicon-o-building-office-2 class="h-6 w-6 text-purple-600 dark:text-purple-400" />
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ __('Branch Reports') }}
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ __('Branch performance, user distribution, and operational metrics') }}
                            </p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <x-filament::button
                            tag="a"
                            href="{{ route('filament.admin.pages.branch-reports') }}"
                            color="primary"
                            size="sm"
                            class="bg-purple-600 hover:bg-purple-700">
                            {{ __('View Reports') }}
                            <x-heroicon-o-arrow-right class="ms-2 h-4 w-4" />
                        </x-filament::button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-filament-panels::page>
