<x-filament-panels::page>
    <div class="flex h-screen bg-gray-50 dark:bg-gray-900">
        <!-- Loading Overlay -->
        <div wire:loading.flex class="fixed inset-0 z-50 items-center justify-center bg-white/90 dark:bg-gray-900/90">
            <div class="flex flex-col items-center gap-3">
                <div class="animate-spin rounded-full h-8 w-8 border-2 border-gray-300 border-t-gray-900 dark:border-gray-700 dark:border-t-gray-100"></div>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Loading...') }}</p>
            </div>
        </div>

        @include('filament.master.pages.pg-admin.sidebar')
        @include('filament.master.pages.pg-admin.main-content')
    </div>
    
    <!-- URL State Management and Sidebar Collapse -->
    <script>
        // Collapse sidebar when Alpine is ready
        document.addEventListener('alpine:init', () => {
            // Wait for Alpine to be fully initialized
            setTimeout(() => {
                if (window.Alpine && Alpine.store('sidebar')) {
                    Alpine.store('sidebar').close();
                }
            }, 50);
        });
        
        document.addEventListener('livewire:initialized', () => {
            // Collapse sidebar after Livewire loads
            setTimeout(() => {
                if (window.Alpine && Alpine.store('sidebar')) {
                    Alpine.store('sidebar').close();
                }
            }, 100);
            
            // Listen for URL updates from Livewire
            Livewire.on('url-updated', (event) => {
                const url = event[0].url || event.url;
                if (url && window.location.href !== url) {
                    window.history.pushState({}, '', url);
                }
            });
            
            // Handle browser back/forward buttons
            window.addEventListener('popstate', () => {
                window.location.reload();
            });
        });
        
        // Also try on DOMContentLoaded as fallback
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                if (window.Alpine && Alpine.store('sidebar')) {
                    Alpine.store('sidebar').close();
                }
            }, 300);
        });
    </script>
</x-filament-panels::page>