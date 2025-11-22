<!-- Table Header -->
<div class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800">
    <div class="px-6 py-3">
        <div class="flex items-center justify-between">
            <!-- Table Info with Tabs -->
            <div class="flex items-center gap-6">
                <div class="flex items-center gap-3">
                    <x-heroicon-o-table-cells class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                    <div>
                        <h1 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                            {{ $selectedTable }}
                        </h1>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            {{ $selectedDatabase }}
                        </p>
                    </div>
                </div>
                
                <!-- Tabs - Button Group (Client-Side Only) -->
                <div class="inline-flex rounded border border-gray-300 dark:border-gray-700 overflow-hidden">
                    <button @click="activeTab = 'data'" 
                            title="{{ __('Table Data') }}"
                            :class="activeTab === 'data' ? 'bg-gray-900 dark:bg-gray-100 text-white dark:text-gray-900' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'"
                            class="flex items-center gap-2 px-3 py-1.5 text-xs font-medium transition-colors border-r border-gray-300 dark:border-gray-700">
                        <x-heroicon-o-table-cells class="w-4 h-4" />
                        {{ __('Data') }}
                    </button>
                    
                    <button @click="activeTab = 'structure'" 
                            title="{{ __('Structure') }}"
                            :class="activeTab === 'structure' ? 'bg-gray-900 dark:bg-gray-100 text-white dark:text-gray-900' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'"
                            class="flex items-center gap-2 px-3 py-1.5 text-xs font-medium transition-colors">
                        <x-heroicon-o-cog-6-tooth class="w-4 h-4" />
                        {{ __('Structure') }}
                    </button>
                </div>
            </div>
            
            <!-- Stats & Controls -->
            <div class="flex items-center gap-6">
                <!-- Compact Stats with Icons -->
                <div class="flex items-center gap-3 text-xs">
                    <div class="flex items-center gap-1 text-gray-600 dark:text-gray-400" title="{{ __('Total Rows') }}">
                        <x-heroicon-o-rectangle-stack class="w-4 h-4" />
                        <span class="font-medium text-gray-900 dark:text-gray-100">{{ number_format($this->getTableRowCount()) }}</span>
                    </div>
                    <div class="flex items-center gap-1 text-gray-600 dark:text-gray-400" title="{{ __('Showing') }}">
                        <x-heroicon-o-eye class="w-4 h-4" />
                        <span class="font-medium text-gray-900 dark:text-gray-100">{{ count($tableData) }}</span>
                    </div>
                    <div class="flex items-center gap-1 text-gray-600 dark:text-gray-400" title="{{ __('Columns') }}">
                        <x-heroicon-o-view-columns class="w-4 h-4" />
                        <span class="font-medium text-gray-900 dark:text-gray-100">{{ count($tableStructure) }}</span>
                    </div>
                </div>
                
                <div class="h-4 w-px bg-gray-200 dark:bg-gray-700"></div>
                
                <!-- Compact Pagination (moved from footer) -->
                <div class="flex items-center gap-2">
                    <!-- Results Info -->
                    <div class="text-xs text-gray-600 dark:text-gray-400 hidden sm:block">
                        {{ __('Showing :from to :to of :total results', [
                            'from' => number_format((($this->getPage() - 1) * $perPage) + 1),
                            'to' => number_format(min($this->getPage() * $perPage, $this->getTableRowCount())),
                            'total' => number_format($this->getTableRowCount())
                        ]) }}
                    </div>

                    <!-- Controls -->
                    <div class="flex items-center gap-1">
                        @if($this->getPage() > 1)
                            <button wire:click="previousPage" 
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center px-2 py-1 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 rounded disabled:opacity-50">
                                <x-heroicon-o-chevron-left class="w-3 h-3" />
                            </button>
                        @endif

                        <!-- Page Input Box -->
                        <div class="flex items-center gap-1">
                            <input type="number" 
                                   wire:model.defer="pageInput" 
                                   wire:keydown.enter="gotoPage($event.target.value)"
                                   min="1" 
                                   max="{{ $this->getTotalPages() }}"
                                   placeholder="{{ $this->getPage() }}"
                                   class="w-12 text-center text-xs rounded border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 focus:border-gray-500 focus:ring-gray-500 py-1">
                            <span class="text-xs text-gray-600 dark:text-gray-400">/ {{ $this->getTotalPages() }}</span>
                        </div>

                        @if($this->getPage() < $this->getTotalPages())
                            <button wire:click="nextPage" 
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center px-2 py-1 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 rounded disabled:opacity-50">
                                <x-heroicon-o-chevron-right class="w-3 h-3" />
                            </button>
                        @endif
                    </div>
                </div>
                
                <div class="h-4 w-px bg-gray-200 dark:bg-gray-700"></div>
                
                <!-- Per Page Selector -->
                <div class="flex items-center gap-2">
                    <label class="text-xs text-gray-600 dark:text-gray-400">{{ __('Show') }}</label>
                    <select wire:model.live="perPage"
                            class="text-xs rounded border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 focus:border-gray-500 focus:ring-gray-500">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
                
                <!-- Refresh Button -->
                <button wire:click="refreshTableData" 
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-50"
                        class="inline-flex items-center gap-2 px-3 py-1.5 border border-gray-300 dark:border-gray-700 rounded text-xs text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                    <x-heroicon-o-arrow-path class="w-3 h-3" />
                    <span wire:loading.remove wire:target="refreshTableData">{{ __('Refresh') }}</span>
                    <span wire:loading wire:target="refreshTableData">{{ __('Refreshing...') }}</span>
                </button>
            </div>
        </div>
    </div>
</div>