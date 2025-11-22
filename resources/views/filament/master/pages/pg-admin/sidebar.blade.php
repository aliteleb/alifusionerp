<!-- Sidebar -->
<div class="w-64 bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-800 overflow-y-auto">
    <!-- Sidebar Header -->
    <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-800">
        <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
            <x-heroicon-o-circle-stack class="w-4 h-4 text-gray-500 dark:text-gray-400" />
            {{ __('Databases') }}
        </h2>
    </div>
    
    <!-- Database List -->
    <div class="p-2">
        @forelse($this->databases as $database)
            <div class="mb-1">
                <!-- Database Item -->
                <div class="flex items-center px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-800 rounded cursor-pointer"
                     wire:click="toggleDatabase('{{ $database }}')"
                     wire:loading.class="opacity-50">
                    
                    <!-- Expand/Collapse Icon -->
                    <div class="mr-2 transition-transform {{ in_array($database, $expandedDatabases) ? 'rotate-90' : '' }}">
                        <x-heroicon-o-chevron-right class="w-3 h-3 text-gray-400 dark:text-gray-500" />
                    </div>
                    
                    <!-- Database Icon -->
                    <x-heroicon-o-server class="w-4 h-4 text-gray-500 dark:text-gray-400 mr-2" />
                    
                    <!-- Database Name -->
                    <div class="flex-1 min-w-0">
                        <span class="text-sm text-gray-700 dark:text-gray-300 truncate block">{{ $database }}</span>
                    </div>
                    
                    <!-- Table Count Badge -->
                    @if(isset($databaseTables[$database]))
                        <span class="text-xs text-gray-500 dark:text-gray-400 ml-2">{{ count($databaseTables[$database]) }}</span>
                    @endif
                    
                    <!-- Loading indicator -->
                    <div wire:loading.flex wire:target="toggleDatabase('{{ $database }}')" class="ml-2">
                        <div class="animate-spin rounded-full h-3 w-3 border border-gray-400 border-t-transparent"></div>
                    </div>
                </div>
                
                <!-- Tables List -->
                @if(in_array($database, $expandedDatabases) && isset($databaseTables[$database]))
                    <div class="ml-5 mt-1 border-l border-gray-200 dark:border-gray-700 pl-3">
                        @forelse($databaseTables[$database] as $table)
                            <div class="flex items-center px-3 py-1.5 mb-0.5 hover:bg-gray-100 dark:hover:bg-gray-800 rounded cursor-pointer {{ $selectedTable === $table && $selectedDatabase === $database ? 'bg-gray-900 dark:bg-gray-100' : '' }}"
                                 wire:click="selectTable('{{ $database }}', '{{ $table }}')"
                                 wire:loading.class="opacity-50">
                                
                                <!-- Table Icon -->
                                <x-heroicon-o-table-cells class="w-3 h-3 {{ $selectedTable === $table && $selectedDatabase === $database ? 'text-white dark:text-gray-900' : 'text-gray-400 dark:text-gray-500' }} mr-2" />
                                
                                <!-- Table Name -->
                                <span class="text-xs {{ $selectedTable === $table && $selectedDatabase === $database ? 'text-white dark:text-gray-900 font-medium' : 'text-gray-600 dark:text-gray-400' }} truncate flex-1">{{ $table }}</span>
                                
                                <!-- Loading indicator -->
                                <div wire:loading.flex wire:target="selectTable('{{ $database }}', '{{ $table }}')" class="ml-2">
                                    <div class="animate-spin rounded-full h-2 w-2 border border-gray-400 border-t-transparent"></div>
                                </div>
                            </div>
                        @empty
                            <div class="px-3 py-4 text-center">
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('No tables found') }}</p>
                            </div>
                        @endforelse
                    </div>
                @endif
            </div>
        @empty
            <div class="p-8 text-center">
                <x-heroicon-o-server class="w-12 h-12 text-gray-300 dark:text-gray-600 mx-auto mb-3" />
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('No databases found') }}</p>
            </div>
        @endforelse
    </div>
</div>