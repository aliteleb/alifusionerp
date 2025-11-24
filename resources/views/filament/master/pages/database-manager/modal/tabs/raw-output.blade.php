{{-- Raw Output Tab Content --}}
<div x-show="activeTab === 'raw'" class="p-6">
    <div class="mb-4">
        <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">{{ __('Command Output') }}</h4>
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Raw output from php artisan migrate:status command') }}</p>
    </div>
    
    {{-- Debug Info --}}
    <div class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
        <h5 class="text-sm font-medium text-blue-900 dark:text-blue-100 mb-2">{{ __('Debug Information') }}</h5>
        <div class="text-xs text-blue-800 dark:text-blue-200 space-y-1">
            <div><strong>{{ __('Facility') }}:</strong> <span x-text="facilityName"></span></div>
            <div><strong>{{ __('Connection') }}:</strong> <span x-text="connectionName"></span></div>
            <div><strong>{{ __('Migrations Found') }}:</strong> <span x-text="migrations.length"></span></div>
            <div><strong>{{ __('Output Length') }}:</strong> <span x-text="rawOutput.length + ' characters'"></span></div>
        </div>
    </div>
    
    {{-- Raw Output Display --}}
    <div class="bg-gray-900 text-green-400 p-4 rounded-lg font-mono text-sm whitespace-pre-wrap overflow-x-auto border border-gray-700 shadow-inner" 
         style="background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%); color: #00ff41; text-shadow: 0 0 3px rgba(0, 255, 65, 0.3); line-height: 1.4;">
        <div x-show="rawOutput.trim().length === 0" class="text-yellow-400 italic">
            {{ __('No output received from migrate:status command') }}
        </div>
        <div x-show="rawOutput.trim().length > 0" x-text="rawOutput"></div>
    </div>
    
    {{-- Help Text --}}
    <div class="mt-4 p-3 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg">
        <h5 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">{{ __('Troubleshooting Tips') }}</h5>
        <ul class="text-xs text-gray-600 dark:text-gray-400 space-y-1 list-disc list-inside">
            <li>{{ __('If output is empty, the migration table may not exist yet') }}</li>
            <li>{{ __('Run migrations first: php artisan migrate --database=tenant_connection') }}</li>
            <li>{{ __('Check if tenant migrations directory exists: Modules/Core/database/migrations/') }}</li>
            <li>{{ __('Verify database connection settings for this tenant') }}</li>
        </ul>
    </div>
</div>