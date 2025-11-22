{{-- Migration Result Modal Component --}}
<div x-data="{
    showModal: false,
    facilityName: '',
    connectionName: '',
    output: '',
    status: 'success',
    message: ''
}"
     @open-migration-result-modal.window="let data = $event.detail || {}; if (Array.isArray(data) && data.length > 0) { data = data[0]; } showModal = true; facilityName = data.facilityName || 'Unknown Facility'; connectionName = data.connectionName || 'Unknown Connection'; output = data.output || 'No output available'; status = data.status || 'success'; message = data.message || 'Operation completed';"
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
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-2xl max-w-6xl w-full max-h-[90vh] overflow-hidden border border-gray-200 dark:border-gray-700 transform transition-all duration-300">
            
            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-3">
                    <div class="grid place-items-center">
                        <x-heroicon-o-circle-stack class="h-6 w-6 text-gray-400 dark:text-gray-500"/>
                    </div>
                    <div class="grid gap-y-1">
                        <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-white">
                            {{ __('Migration Result') }}
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ __('Facility: ') }}<span x-text="facilityName"></span>
                        </p>
                    </div>
                </div>
                <button @click="showModal = false" class="text-gray-400 hover:text-gray-500 dark:text-gray-500 dark:hover:text-gray-400">
                    <x-heroicon-o-x-mark class="h-5 w-5"/>
                </button>
            </div>
            
            {{-- Status Message --}}
            <div :class="{
                'bg-green-50 border-green-200 dark:bg-green-900/20 dark:border-green-800': status === 'success',
                'bg-red-50 border-red-200 dark:bg-red-900/20 dark:border-red-800': status === 'error'
            }" class="px-6 py-4 border-b">
                <div class="flex items-center gap-3">
                    <div :class="{
                        'text-green-500 dark:text-green-400': status === 'success',
                        'text-red-500 dark:text-red-400': status === 'error'
                    }">
                        <x-heroicon-o-check-circle x-show="status === 'success'" class="h-6 w-6"/>
                        <x-heroicon-o-x-circle x-show="status === 'error'" class="h-6 w-6"/>
                    </div>
                    <div>
                        <h4 :class="{
                            'text-green-800 dark:text-green-200': status === 'success',
                            'text-red-800 dark:text-red-200': status === 'error'
                        }" class="text-sm font-medium">
                            <span x-text="message"></span>
                        </h4>
                    </div>
                </div>
            </div>
            
            {{-- Output Content --}}
            <div class="px-6 py-4 overflow-auto max-h-[60vh]">
                <div class="mb-4">
                    <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">
                        {{ __('Migration Output') }}
                    </h4>
                    <pre x-text="output" class="text-xs bg-gray-50 dark:bg-gray-800 p-4 rounded-lg overflow-auto max-h-96 whitespace-pre-wrap"></pre>
                </div>
            </div>
            
            {{-- Footer --}}
            <div class="flex items-center justify-end gap-3 px-6 py-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-200 dark:border-gray-700">
                <button @click="showModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800">
                    {{ __('Close') }}
                </button>
            </div>
        </div>
    </div>
</div>