{{-- Modal Footer Component --}}
<div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 flex justify-between items-center">
    <div class="text-xs text-gray-500 dark:text-gray-400">
        {{ __('Last updated') }}: <span x-text="new Date().toLocaleString()"></span>
    </div>
    <div class="flex space-x-3">
        <x-filament::button @click="location.reload()" color="gray" size="sm">
            <x-heroicon-o-arrow-path class="h-4 w-4 mr-1"/>
            {{ __('Refresh') }}
        </x-filament::button>
        <x-filament::button @click="showModal = false" color="primary" size="sm">
            {{ __('Close') }}
        </x-filament::button>
    </div>
</div>