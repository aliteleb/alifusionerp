{{-- Migration Status Component --}}
@if($db['exists'] && $db['can_connect'] && $db['migration_status'])
    @if($db['migration_status']['pending'] > 0)
        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
            <x-heroicon-o-exclamation-triangle class="h-3 w-3 mr-1"/>
            {{ $db['migration_status']['pending'] }} {{ __('pending') }}
        </span>
    @else
        <span class="w-max inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
            <x-heroicon-o-check-circle class="h-3 w-3 mr-1"/>
            {{ __('Up to date') }}
        </span>
    @endif
@else
    -
@endif