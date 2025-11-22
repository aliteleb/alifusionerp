<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Tenant Databases Overview --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="fi-section-header flex flex-col gap-3 px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="grid place-items-center">
                        <x-heroicon-o-circle-stack class="h-6 w-6 text-gray-400 dark:text-gray-500"/>
                    </div>
                    <div class="grid flex-1 gap-y-1">
                        <h3 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">
                            {{ __('Tenant Databases') }}
                        </h3>
                        <p class="fi-section-header-description text-sm text-gray-500 dark:text-gray-400">
                            {{ __('Manage individual tenant databases for each facility') }}
                        </p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <x-filament::button
                            wire:click="refreshStatus"
                            icon="heroicon-o-arrow-path"
                            size="sm"
                            color="gray">
                            {{ __('Refresh') }}
                        </x-filament::button>
                    </div>
                </div>
            </div>
            <div class="fi-section-content px-6 py-4">
                @php
                    $tenantDatabases = $this->getTenantDatabases();
                @endphp
                
                @if(count($tenantDatabases) > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            @include('filament.master.pages.database-manager.table-header')
                            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
                                @foreach($tenantDatabases as $db)
                                    @include('filament.master.pages.database-manager.table-row', ['db' => $db])
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-8">
                        <x-heroicon-o-circle-stack class="mx-auto h-12 w-12 text-gray-400"/>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('No facilities found') }}</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Create a facility first to manage its tenant database.') }}</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Database Summary Stats --}}
        @include('filament.master.pages.database-manager.summary-stats', ['tenantDatabases' => $tenantDatabases])
    </div>

    {{-- Migration Status Modal --}}
    @include('filament.master.pages.database-manager.migration-modal')
    
    {{-- Migration Result Modal --}}
    @include('filament.master.pages.database-manager.migration-result-modal')
    
    {{-- Backup Manager Modal --}}
    @include('filament.master.pages.database-manager.backup-modal')
    
    {{-- Backup Test Modal --}}
    @include('filament.master.pages.database-manager.backup-test-modal')
</x-filament-panels::page>