<!-- Welcome State -->
<div class="flex-1 flex items-center justify-center min-h-screen bg-gray-50 dark:bg-gray-900 p-6">
    <div class="text-center max-w-2xl">
        <!-- Icon -->
        <div class="mx-auto mb-6 w-16 h-16 bg-gray-200 dark:bg-gray-800 rounded-lg flex items-center justify-center">
            <x-heroicon-o-circle-stack class="w-8 h-8 text-gray-500 dark:text-gray-400" />
        </div>
        
        <!-- Title -->
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100 mb-2">
            {{ __('Welcome to PgAdmin') }}
        </h1>
        
        <!-- Description -->
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-8">
            {{ __('Select a database and table from the sidebar to start exploring your PostgreSQL data.') }}
        </p>
        
        <!-- Features -->
        <div class="grid grid-cols-2 gap-4 mb-8">
            <div class="bg-white dark:bg-gray-800 p-4 rounded border border-gray-200 dark:border-gray-700">
                <x-heroicon-o-eye class="w-5 h-5 text-gray-500 dark:text-gray-400 mx-auto mb-2" />
                <p class="text-sm text-gray-700 dark:text-gray-300 font-medium mb-1">{{ __('Browse Data') }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('View and navigate table data') }}</p>
            </div>
            
            <div class="bg-white dark:bg-gray-800 p-4 rounded border border-gray-200 dark:border-gray-700">
                <x-heroicon-o-pencil class="w-5 h-5 text-gray-500 dark:text-gray-400 mx-auto mb-2" />
                <p class="text-sm text-gray-700 dark:text-gray-300 font-medium mb-1">{{ __('Edit Records') }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Modify table records') }}</p>
            </div>
        </div>
        
        <!-- Getting Started -->
        <div class="bg-white dark:bg-gray-800 rounded border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center justify-center gap-2 mb-3">
                <x-heroicon-o-light-bulb class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('Getting Started') }}</h3>
            </div>
            
            <p class="text-xs text-gray-600 dark:text-gray-400">
                {{ __('Click on any database in the sidebar to expand it and view its tables.') }}
            </p>
        </div>
    </div>
</div>