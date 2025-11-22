{{-- Tab Navigation Component --}}
<div class="border-b border-gray-200 dark:border-gray-700">
    <nav class="flex space-x-8 px-6" aria-label="Tabs">
        <button @click="activeTab = 'overview'" 
                :class="activeTab === 'overview' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
            {{ __('Overview') }}
        </button>
        <button @click="activeTab = 'details'" 
                :class="activeTab === 'details' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
            {{ __('Migration Details') }}
        </button>
        <button @click="activeTab = 'raw'" 
                :class="activeTab === 'raw' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
            {{ __('Raw Output') }}
        </button>
    </nav>
</div>