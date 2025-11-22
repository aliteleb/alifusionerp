<!-- Table Content -->
<div class="flex-1 overflow-auto bg-gray-50 dark:bg-gray-900">
    <!-- Data Tab -->
    <div x-show="activeTab === 'data'" style="height: calc(100vh - 170px);">
        @include('filament.master.pages.pg-admin.table-data')
    </div>
    
    <!-- Structure Tab -->
    <div x-show="activeTab === 'structure'" style="height: calc(100vh - 170px);">
        @include('filament.master.pages.pg-admin.table-structure')
    </div>
</div>