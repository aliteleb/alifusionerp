<!-- Main Content -->
<div class="flex-1 flex flex-col overflow-hidden" 
     x-data="{ activeTab: 'data' }"
     x-init="
        // Reset tab to 'data' when table changes
        $watch('$wire.selectedTable', () => {
            activeTab = 'data';
        });
     ">
    @if($selectedTable && $selectedDatabase)
        <!-- Loading State for Table Data -->
        <div wire:loading.flex wire:target="loadTableDataAsync,selectTable,restoreFromUrlParams" class="flex-1 flex items-center justify-center bg-gray-50 dark:bg-gray-900">
            <div class="text-center">
                <div class="animate-spin rounded-full h-8 w-8 border-2 border-gray-300 border-t-gray-900 dark:border-gray-700 dark:border-t-gray-100 mx-auto mb-3"></div>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Loading...') }}</p>
            </div>
        </div>
        
        <!-- Show loading if isLoadingTableData is true (for URL restoration) -->
        @if($isLoadingTableData)
            <div class="flex-1 flex items-center justify-center bg-gray-50 dark:bg-gray-900">
                <div class="text-center">
                    <div class="animate-spin rounded-full h-8 w-8 border-2 border-gray-300 border-t-gray-900 dark:border-gray-700 dark:border-t-gray-100 mx-auto mb-3"></div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Loading...') }}</p>
                </div>
            </div>
        @else
            <!-- Table Content -->
            <div wire:loading.remove wire:target="loadTableDataAsync,selectTable,restoreFromUrlParams" 
                 wire:key="content-{{ $selectedDatabase }}-{{ $selectedTable }}">
                @include('filament.master.pages.pg-admin.table-header')
                @include('filament.master.pages.pg-admin.table-content')
            </div>
        @endif
    @else
        @include('filament.master.pages.pg-admin.welcome-state')
    @endif
</div>