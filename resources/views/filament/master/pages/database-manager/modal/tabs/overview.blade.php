{{-- Overview Tab Content --}}
<div x-show="activeTab === 'overview'" class="p-6">
    <div x-show="migrations.length > 0">
        <div class="flex items-center justify-between mb-6">
            <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('Recent Migrations') }}</h4>
            <span class="text-sm text-gray-500 dark:text-gray-400" x-text="'Showing ' + Math.min(10, migrations.length) + ' of ' + migrations.length + ' migrations'"></span>
        </div>
        <div class="space-y-3">
            <template x-for="(migration, index) in migrations.slice(-10).reverse()" :key="index">
                <div class="group relative flex items-center justify-between p-4 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-700 rounded-xl border border-gray-200 dark:border-gray-600 hover:shadow-md transition-all duration-200">
                    <div class="flex items-center space-x-4">
                        <div class="relative">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center transition-all duration-200" 
                                 :class="migration.ran ? 'bg-green-100 dark:bg-green-900 text-green-600 dark:text-green-400' : 'bg-yellow-100 dark:bg-yellow-900 text-yellow-600 dark:text-yellow-400'">
                                <x-heroicon-o-check class="h-5 w-5" x-show="migration.ran"/>
                                <x-heroicon-o-clock class="h-5 w-5" x-show="!migration.ran"/>
                            </div>
                            <div x-show="migration.ran" class="absolute -top-1 -right-1 w-3 h-3 bg-green-500 rounded-full ring-2 ring-white dark:ring-gray-800"></div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate font-mono" x-text="migration.name"></div>
                            <div class="flex items-center space-x-2 mt-1">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium" 
                                      :class="migration.ran ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'">
                                    <span x-text="migration.ran ? 'Completed' : 'Pending'"></span>
                                </span>
                                <span x-show="migration.batch" class="text-xs text-gray-500 dark:text-gray-400" x-text="'Batch ' + migration.batch"></span>
                            </div>
                        </div>
                    </div>
                    <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                        <x-heroicon-o-chevron-right class="h-4 w-4 text-gray-400"/>
                    </div>
                </div>
            </template>
        </div>
        <div x-show="migrations.length > 10" class="mt-4 text-center">
            <button @click="activeTab = 'details'" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200 text-sm font-medium transition-colors">
                {{ __('View all migrations') }} →
            </button>
        </div>
    </div>
    <div x-show="migrations.length === 0" class="text-center py-12">
        <div class="mx-auto w-20 h-20 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mb-4">
            <x-heroicon-o-document-text class="h-10 w-10 text-gray-400"/>
        </div>
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">{{ __('No migrations found') }}</h3>
        <div class="text-sm text-gray-500 dark:text-gray-400 max-w-sm mx-auto space-y-2">
            <p>{{ __('This could mean:') }}</p>
            <ul class="list-disc list-inside text-left space-y-1">
                <li>{{ __('No tenant migration files exist yet') }}</li>
                <li>{{ __('Migration table is not initialized') }}</li>
                <li>{{ __('Database connection issue') }}</li>
                <li>{{ __('Tenant migrations path is incorrect') }}</li>
            </ul>
            <p class="mt-3">
                <button @click="activeTab = 'raw'" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200 text-sm font-medium">
                    {{ __('View raw output for details') }} →
                </button>
            </p>
        </div>
    </div>
</div>