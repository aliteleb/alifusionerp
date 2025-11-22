{{-- Backup Test Modal Component --}}
<div x-data="{
    showModal: false,
    facilityId: null,
    results: {}
}"
     @open-backup-test-modal.window="const data = $event.detail[0] || {}; showModal = true; facilityId = data.facilityId; results = data.results || {};"
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
                            {{ __('Backup Environment Test') }}
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            {{ __('Diagnostic results for PostgreSQL backup configuration') }}
                        </p>
                    </div>
                    <button @click="showModal = false" 
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                        <x-heroicon-o-x-mark class="h-6 w-6"/>
                    </button>
                </div>
            </div>
            
            {{-- Content --}}
            <div class="p-6 max-h-[70vh] overflow-y-auto">
                {{-- Connection Info --}}
                <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-3">{{ __('Connection Configuration') }}</h3>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="font-medium text-gray-700 dark:text-gray-300">{{ __('Driver') }}:</span>
                            <span class="ml-2 text-gray-900 dark:text-gray-100" x-text="results.driver || 'N/A'"></span>
                        </div>
                        <div>
                            <span class="font-medium text-gray-700 dark:text-gray-300">{{ __('Host') }}:</span>
                            <span class="ml-2 text-gray-900 dark:text-gray-100" x-text="results.host || 'N/A'"></span>
                        </div>
                        <div>
                            <span class="font-medium text-gray-700 dark:text-gray-300">{{ __('Port') }}:</span>
                            <span class="ml-2 text-gray-900 dark:text-gray-100" x-text="results.port || 'N/A'"></span>
                        </div>
                        <div>
                            <span class="font-medium text-gray-700 dark:text-gray-300">{{ __('Database') }}:</span>
                            <span class="ml-2 text-gray-900 dark:text-gray-100" x-text="results.database || 'N/A'"></span>
                        </div>
                        <div>
                            <span class="font-medium text-gray-700 dark:text-gray-300">{{ __('Username') }}:</span>
                            <span class="ml-2 text-gray-900 dark:text-gray-100" x-text="results.username || 'N/A'"></span>
                        </div>
                    </div>
                </div>

                {{-- Test Results --}}
                <div class="space-y-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('Test Results') }}</h3>
                    
                    <template x-for="(test, testName) in results.tests || {}" :key="testName">
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="font-medium text-gray-900 dark:text-gray-100" x-text="testName.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())"></h4>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                      :class="test.success ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'">
                                    <span x-show="test.success">✓ {{ __('Passed') }}</span>
                                    <span x-show="!test.success">✗ {{ __('Failed') }}</span>
                                </span>
                            </div>
                            
                            <div x-show="test.output" class="mb-2">
                                <p class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Output') }}:</p>
                                <pre class="text-xs bg-gray-100 dark:bg-gray-800 p-2 rounded overflow-x-auto" x-text="test.output"></pre>
                            </div>
                            
                            <div x-show="test.error" class="mb-2">
                                <p class="text-xs font-medium text-red-700 dark:text-red-300 mb-1">{{ __('Error') }}:</p>
                                <pre class="text-xs bg-red-50 dark:bg-red-900/20 p-2 rounded overflow-x-auto text-red-800 dark:text-red-200" x-text="test.error"></pre>
                            </div>
                            
                            <div x-show="test.path" class="mb-2">
                                <p class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Path') }}:</p>
                                <code class="text-xs bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded" x-text="test.path"></code>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Troubleshooting Tips --}}
                <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                    <h3 class="text-lg font-medium text-blue-900 dark:text-blue-100 mb-3">{{ __('Troubleshooting Tips') }}</h3>
                    <ul class="text-sm text-blue-800 dark:text-blue-200 space-y-2">
                        <li>• {{ __('If pg_dump is not available, the system will use Laravel fallback backup (less efficient but functional)') }}</li>
                        <li>• {{ __('To install PostgreSQL tools: Download from postgresql.org and add bin folder to system PATH') }}</li>
                        <li>• {{ __('If server is not ready, check PostgreSQL service status') }}</li>
                        <li>• {{ __('If connection fails, verify database credentials and network connectivity') }}</li>
                        <li>• {{ __('If backup directory is not writable, check file permissions') }}</li>
                        <li>• {{ __('For timeout issues, check if pg_hba.conf allows connections from your server') }}</li>
                    </ul>
                </div>
            </div>
            
            {{-- Footer --}}
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 flex justify-end">
                <x-filament::button @click="showModal = false" color="primary" size="sm">
                    {{ __('Close') }}
                </x-filament::button>
            </div>
        </div>
    </div>
</div>