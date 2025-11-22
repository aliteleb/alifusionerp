{{-- Backup Manager Modal Component --}}
<div x-data="{
    showModal: false,
    facilityId: null,
    facilityName: '',
    backups: [],
    loading: false,
    selectedBackup: null
}"
     @open-backup-manager-modal.window="const data = $event.detail[0] || {}; showModal = true; facilityId = data.facilityId; backups = data.backups || []; selectedBackup = null; const facilityElement = document.querySelector(`[data-facility-id='${data.facilityId}']`); facilityName = facilityElement ? facilityElement.dataset.facilityName : 'Unknown Facility';"
     @backup-created.window="const data = $event.detail[0] || {}; if (showModal && facilityId == data.facilityId) { $wire.listTenantBackups(facilityId); }"
     x-show="showModal"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform scale-95"
     x-transition:enter-end="opacity-100 transform scale-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 transform scale-100"
     x-transition:leave-end="opacity-0 transform scale-95"
     class="fixed inset-0 z-50 overflow-y-auto"
     style="display: none;"
     @keydown.escape.window="showModal = false">
    
    {{-- Backdrop --}}
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-40" @click="showModal = false"></div>
    
    {{-- Modal Content --}}
    <div class="relative z-50 flex min-h-screen items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden border border-gray-200 dark:border-gray-700 transform transition-all duration-300">
            
            {{-- Header --}}
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                            {{ __('Backup Manager') }}
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1" x-text="facilityName"></p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <x-filament::button 
                            @click="$wire.backupTenantDatabase(facilityId)"
                            color="success" 
                            size="sm"
                            icon="heroicon-o-plus"
                            wire:loading.attr="disabled"
                            :wire:target="'backupTenantDatabase'">
                            <span wire:loading.remove wire:target="backupTenantDatabase">{{ __('Create Backup') }}</span>
                            <span wire:loading wire:target="backupTenantDatabase">{{ __('Creating...') }}</span>
                        </x-filament::button>
                        <button @click="showModal = false" 
                                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                            <x-heroicon-o-x-mark class="h-6 w-6"/>
                        </button>
                    </div>
                </div>
            </div>
            
            {{-- Content --}}
            <div class="p-6 max-h-[70vh] overflow-y-auto">
                <div x-show="backups.length === 0" class="text-center py-12">
                    <x-heroicon-o-archive-box class="mx-auto h-12 w-12 text-gray-400"/>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('No backups found') }}</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Create your first backup to get started.') }}</p>
                </div>
                
                <div x-show="backups.length > 0" class="space-y-4">
                    <div class="grid gap-4">
                        <template x-for="backup in backups" :key="backup.filename">
                            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-3">
                                            <x-heroicon-o-document class="h-5 w-5 text-gray-400"/>
                                            <div>
                                                <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="backup.filename"></h4>
                                                <div class="flex items-center space-x-4 mt-1">
                                                    <span class="text-xs text-gray-500 dark:text-gray-400" x-text="backup.human_date"></span>
                                                    <span class="text-xs text-gray-500 dark:text-gray-400" x-text="backup.human_size"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <x-filament::button 
                                            @click="
                                                if (confirm('{{ __('Are you sure you want to restore this backup? This will replace the current database and cannot be undone!') }}')) {
                                                    $wire.restoreTenantDatabase(facilityId, backup.filename);
                                                    showModal = false;
                                                }
                                            "
                                            color="primary" 
                                            size="xs"
                                            icon="heroicon-o-arrow-path">
                                            {{ __('Restore') }}
                                        </x-filament::button>
                                        <x-filament::button 
                                            @click="
                                                if (confirm('{{ __('Are you sure you want to delete this backup? This action cannot be undone!') }}')) {
                                                    $wire.deleteTenantBackup(facilityId, backup.filename);
                                                }
                                            "
                                            color="danger" 
                                            size="xs"
                                            icon="heroicon-o-trash">
                                            {{ __('Delete') }}
                                        </x-filament::button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
            
            {{-- Footer --}}
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 flex justify-between items-center">
                <div class="text-xs text-gray-500 dark:text-gray-400">
                    {{ __('Backup files are stored in') }}: <code class="bg-gray-200 dark:bg-gray-700 px-1 rounded">storage/app/backups/tenant-databases/</code>
                </div>
                <div class="flex space-x-3">
                    <x-filament::button @click="$wire.listTenantBackups(facilityId)" color="gray" size="sm">
                        <x-heroicon-o-arrow-path class="h-4 w-4 mr-1"/>
                        {{ __('Refresh') }}
                    </x-filament::button>
                    <x-filament::button @click="showModal = false" color="primary" size="sm">
                        {{ __('Close') }}
                    </x-filament::button>
                </div>
            </div>
        </div>
    </div>
</div>