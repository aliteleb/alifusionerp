{{-- Summary Statistics Component --}}
<div class="grid grid-cols-1 md:grid-cols-4 gap-4">
    @php
        $totalFacilities = count($tenantDatabases);
        $existingDatabases = collect($tenantDatabases)->where('exists', true)->count();
        $connectedDatabases = collect($tenantDatabases)->where('can_connect', true)->count();
        $errorDatabases = collect($tenantDatabases)->where('error', '!=', null)->count();
    @endphp
    
    <div class="bg-white dark:bg-gray-900 rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <x-heroicon-o-building-office class="h-8 w-8 text-blue-600"/>
            </div>
            <div class="ml-5 w-0 flex-1">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                        {{ __('Total Facilities') }}
                    </dt>
                    <dd class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        {{ $totalFacilities }}
                    </dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-900 rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <x-heroicon-o-circle-stack class="h-8 w-8 text-green-600"/>
            </div>
            <div class="ml-5 w-0 flex-1">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                        {{ __('Existing Databases') }}
                    </dt>
                    <dd class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        {{ $existingDatabases }}
                    </dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-900 rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <x-heroicon-o-signal class="h-8 w-8 text-purple-600"/>
            </div>
            <div class="ml-5 w-0 flex-1">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                        {{ __('Connected Databases') }}
                    </dt>
                    <dd class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        {{ $connectedDatabases }}
                    </dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-900 rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <x-heroicon-o-exclamation-triangle class="h-8 w-8 text-red-600"/>
            </div>
            <div class="ml-5 w-0 flex-1">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                        {{ __('Databases with Errors') }}
                    </dt>
                    <dd class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        {{ $errorDatabases }}
                    </dd>
                </dl>
            </div>
        </div>
    </div>
</div>