{{-- Database Manager Table Row --}}
<tr class="hover:bg-gray-50 dark:hover:bg-gray-800" 
    data-facility-id="{{ $db['facility']->id }}" 
    data-facility-name="{{ $db['facility']->name }}">
    <td class="px-6 py-4 whitespace-nowrap">
        <div class="flex items-center">
            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                <x-heroicon-o-building-office class="h-5 w-5 text-blue-600 dark:text-blue-400"/>
            </div>
            <div class="ml-4">
                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                    {{ $db['facility']->name }}
                </div>
                @if($db['facility']->subdomain)
                    <div class="text-sm text-gray-500 dark:text-gray-400 flex items-center">
                        <x-heroicon-o-globe-alt class="h-3 w-3 mr-1"/>
                        {{ $db['facility']->subdomain }}.{{ config('app.domain', 'localhost') }}
                    </div>
                @endif
            </div>
        </div>
    </td>
    <td class="px-6 py-4 whitespace-nowrap">
        <div class="flex items-center">
            <x-heroicon-o-circle-stack class="h-4 w-4 text-gray-400 mr-2"/>
            <div>
                <div class="text-sm text-gray-900 dark:text-gray-100">
                    {{ $db['database_name'] }}
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400 flex items-center">
                    <x-heroicon-o-link class="h-3 w-3 mr-1"/>
                    {{ $db['connection_name'] }}
                </div>
            </div>
        </div>
    </td>
    <td class="px-6 py-4 whitespace-nowrap">
        @include('filament.master.pages.database-manager.status-badges', ['db' => $db])
    </td>
    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
        @if($db['exists'] && $db['can_connect'])
            <div class="flex items-center">
                <x-heroicon-o-table-cells class="h-4 w-4 mr-2"/>
                {{ $db['table_count'] }} {{ __('tables') }}
            </div>
        @else
            <div class="flex items-center text-gray-400">
                <x-heroicon-o-x-circle class="h-4 w-4 mr-2"/>
                -
            </div>
        @endif
    </td>
    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
        @include('filament.master.pages.database-manager.migration-status', ['db' => $db])
    </td>
    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
        @include('filament.master.pages.database-manager.action-buttons', ['db' => $db])
    </td>
</tr>