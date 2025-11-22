{{-- Action Buttons Component --}}
<div class="flex flex-wrap gap-2">
    @if(!$db['exists'])
        <x-filament::button
            wire:click="createTenantDatabase({{ $db['facility']->id }})"
            wire:confirm="{{ __('Are you sure you want to create a database for :facility? This will create a new tenant database.', ['facility' => $db['facility']->name]) }}"
            color="success"
            size="xs"
            icon="heroicon-o-plus">
            {{ __('Create') }}
        </x-filament::button>
    @else
        {{-- Primary Actions --}}
        <x-filament::button
            wire:click="testTenantConnection({{ $db['facility']->id }})"
            color="gray"
            size="xs"
            icon="heroicon-o-signal">
            {{ __('Test') }}
        </x-filament::button>
        
        <x-filament::button
            wire:click="checkMigrationStatus({{ $db['facility']->id }})"
            color="info"
            size="xs"
            icon="heroicon-o-list-bullet"
            wire:loading.attr="disabled"
            wire:target="checkMigrationStatus({{ $db['facility']->id }})">
            <span wire:loading.remove wire:target="checkMigrationStatus({{ $db['facility']->id }})">{{ __('Status') }}</span>
            <span wire:loading wire:target="checkMigrationStatus({{ $db['facility']->id }})">{{ __('Loading...') }}</span>
        </x-filament::button>
        
        <x-filament::button
            wire:click="runTenantMigration({{ $db['facility']->id }})"
            wire:confirm="{{ __('Are you sure you want to run migrations for :facility? This will update the database schema.', ['facility' => $db['facility']->name]) }}"
            color="primary"
            size="xs"
            icon="heroicon-o-arrow-up">
            {{ __('Migrate') }}
        </x-filament::button>

        <x-filament::button
            wire:click="rollbackTenantMigration({{ $db['facility']->id }})"
            wire:confirm="{{ __('Are you sure you want to rollback migrations for :facility? This will revert the database schema changes.', ['facility' => $db['facility']->name]) }}"
            color="warning"
            size="xs"
            icon="heroicon-o-arrow-uturn-left">
            {{ __('Rollback') }}
        </x-filament::button>
        
        {{-- Dropdown for additional actions --}}
        <x-filament::dropdown>
            <x-slot name="trigger">
                <x-filament::button
                    color="secondary"
                    size="xs"
                    icon="heroicon-o-ellipsis-horizontal">
                    {{ __('More') }}
                </x-filament::button>
            </x-slot>
            
            <x-filament::dropdown.list>
                <x-filament::dropdown.list.item
                    wire:click="seedTenantDatabase({{ $db['facility']->id }})"
                    wire:confirm="{{ __('Are you sure you want to seed the database for :facility? This will add default data and may modify existing records.', ['facility' => $db['facility']->name]) }}"
                    color="warning"
                    icon="heroicon-o-rectangle-stack">
                    {{ __('Seed Database') }}
                </x-filament::dropdown.list.item>
                
                <x-filament::dropdown.list.item
                    wire:click="listTenantBackups({{ $db['facility']->id }})"
                    icon="heroicon-o-folder-open">
                    {{ __('Manage Backups') }}
                </x-filament::dropdown.list.item>
                
                <x-filament::dropdown.list.item
                    wire:click="testBackupEnvironment({{ $db['facility']->id }})"
                    icon="heroicon-o-wrench-screwdriver">
                    {{ __('Test Backup Environment') }}
                </x-filament::dropdown.list.item>
                
                {{-- <x-filament::dropdown.list.item
                    wire:click="backupTenantDatabase({{ $db['facility']->id }})"
                    wire:confirm="{{ __('Are you sure you want to create a backup for :facility? This will create a database backup file.', ['facility' => $db['facility']->name]) }}"
                    icon="heroicon-o-archive-box">
                    {{ __('Create Backup') }}
                </x-filament::dropdown.list.item> --}}
                
                {{-- <x-filament::dropdown.list.item
                    wire:click="dropTenantDatabase({{ $db['facility']->id }})"
                    wire:confirm="{{ __('DANGER: Are you absolutely sure you want to permanently delete the database for :facility? This action will destroy ALL data and CANNOT be undone!', ['facility' => $db['facility']->name]) }}"
                    color="danger"
                    icon="heroicon-o-trash">
                    {{ __('Drop Database') }}
                </x-filament::dropdown.list.item> --}}
            </x-filament::dropdown.list>
        </x-filament::dropdown>
    @endif
</div>