{{-- Details Tab Content --}}
<div x-show="activeTab === 'details'" class="p-6">
    <div x-show="migrations.length > 0">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Status') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Migration') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Batch') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                    <template x-for="(migration, index) in migrations" :key="index">
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" 
                                      :class="migration.ran ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'">
                                    <span x-text="migration.ran ? 'Completed' : 'Pending'"></span>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100 font-mono" x-text="migration.name"></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400" x-text="migration.batch || '-'"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
    <div x-show="migrations.length === 0" class="text-center py-8">
        <div class="mx-auto w-16 h-16 bg-yellow-100 dark:bg-yellow-900 rounded-full flex items-center justify-center mb-4">
            <x-heroicon-o-exclamation-triangle class="h-8 w-8 text-yellow-600 dark:text-yellow-400"/>
        </div>
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">{{ __('No migration details available') }}</h3>
        <div class="text-sm text-gray-500 dark:text-gray-400 max-w-md mx-auto space-y-3">
            <p>{{ __('Unable to parse migration details from the command output.') }}</p>
            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-3">
                <p class="text-yellow-800 dark:text-yellow-200 text-xs">
                    <strong>{{ __('Troubleshooting:') }}</strong><br>
                    {{ __('Check the raw output tab for the actual command response, or try running the migration manually.') }}
                </p>
            </div>
            <button @click="activeTab = 'raw'" class="inline-flex items-center px-3 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 text-sm rounded-md hover:bg-blue-200 dark:hover:bg-blue-800 transition-colors">
                {{ __('View Raw Output') }}
            </button>
        </div>
    </div>
</div>