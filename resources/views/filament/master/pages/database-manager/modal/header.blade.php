{{-- Modal Header Component --}}
<div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-800 dark:to-gray-700">
    <div class="flex items-center space-x-4">
        <div class="flex-shrink-0">
            <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-xl flex items-center justify-center shadow-sm">
                <x-heroicon-o-list-bullet class="h-7 w-7 text-blue-600 dark:text-blue-400"/>
            </div>
        </div>
        <div>
            <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100" x-text="'Migration Status - ' + facilityName"></h3>
            <p class="text-sm text-gray-600 dark:text-gray-400" x-text="'Connection: ' + connectionName"></p>
        </div>
    </div>
    <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-all duration-200 p-2 rounded-lg hover:bg-white/50 dark:hover:bg-gray-800/50">
        <x-heroicon-o-x-mark class="h-6 w-6"/>
    </button>
</div>