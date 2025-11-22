{{-- Migration Status Modal Component --}}
<div x-data="{
    showModal: false,
    facilityName: '',
    connectionName: '',
    output: '',
    rawOutput: '',
    summary: '',
    pending: 0,
    ran: 0,
    total: 0,
    migrations: [],
    lastRun: null,
    activeTab: 'overview'
}"
     @open-migration-status-modal.window="const data = $event.detail[0] || {}; showModal = true; facilityName = data.facilityName || 'Unknown Facility'; connectionName = data.connectionName || 'Unknown Connection'; output = data.output || 'No output available'; rawOutput = data.rawOutput || 'No raw output available'; summary = data.summary || 'No summary available'; pending = parseInt(data.pending) || 0; ran = parseInt(data.ran) || 0; total = parseInt(data.total) || 0; migrations = data.migrations || []; lastRun = data.lastRun || null; activeTab = 'overview';"
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
            @include('filament.master.pages.database-manager.modal.header')
            
            {{-- Status Overview Cards --}}
            @include('filament.master.pages.database-manager.modal.status-overview')
            
            {{-- Tab Navigation --}}
            @include('filament.master.pages.database-manager.modal.tab-navigation')
            
            {{-- Tab Content --}}
            @include('filament.master.pages.database-manager.modal.tab-content')
            
            {{-- Footer --}}
            @include('filament.master.pages.database-manager.modal.footer')
        </div>
    </div>
</div>