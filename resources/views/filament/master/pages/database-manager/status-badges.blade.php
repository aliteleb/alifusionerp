{{-- Status Badges Component --}}
<div class="flex flex-col space-y-1">
    @if($db['exists'])
        @if($db['can_connect'])
            <span class="w-max inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                <x-heroicon-o-check-circle class="h-3 w-3 mr-1"/>
                {{ __('Connected') }}
            </span>
        @else
            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                <x-heroicon-o-exclamation-triangle class="h-3 w-3 mr-1"/>
                {{ __('Cannot Connect') }}
            </span>
        @endif
    @else
        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
            <x-heroicon-o-x-circle class="h-3 w-3 mr-1"/>
            {{ __('Not Found') }}
        </span>
    @endif
    @if($db['error'])
        <span class="text-xs text-red-600 dark:text-red-400" title="{{ $db['error'] }}">
            {{ __('Error') }}
        </span>
    @endif
</div>