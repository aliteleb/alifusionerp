{{-- Database Manager Table Header --}}
<thead class="bg-gray-50 dark:bg-gray-800">
    <tr>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
            <div class="flex items-center">
                <x-heroicon-o-building-office class="h-4 w-4 mr-2"/>
                {{ __('Facility') }}
            </div>
        </th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
            <div class="flex items-center">
                <x-heroicon-o-circle-stack class="h-4 w-4 mr-2"/>
                {{ __('Database') }}
            </div>
        </th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
            <div class="flex items-center">
                <x-heroicon-o-signal class="h-4 w-4 mr-2"/>
                {{ __('Status') }}
            </div>
        </th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
            <div class="flex items-center">
                <x-heroicon-o-table-cells class="h-4 w-4 mr-2"/>
                {{ __('Tables') }}
            </div>
        </th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
            <div class="flex items-center">
                <x-heroicon-o-arrow-up-tray class="h-4 w-4 mr-2"/>
                {{ __('Migrations') }}
            </div>
        </th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
            <div class="flex items-center">
                <x-heroicon-o-cog class="h-4 w-4 mr-2"/>
                {{ __('Actions') }}
            </div>
        </th>
    </tr>
</thead>