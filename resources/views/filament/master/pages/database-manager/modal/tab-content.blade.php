{{-- Tab Content Component --}}
<div class="overflow-y-auto" style="max-height: 50vh;">
    
    {{-- Overview Tab --}}
    @include('filament.master.pages.database-manager.modal.tabs.overview')
    
    {{-- Details Tab --}}
    @include('filament.master.pages.database-manager.modal.tabs.details')
    
    {{-- Raw Output Tab --}}
    @include('filament.master.pages.database-manager.modal.tabs.raw-output')
    
</div>