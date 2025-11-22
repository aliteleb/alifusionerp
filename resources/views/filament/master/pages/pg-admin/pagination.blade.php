<!-- Pagination -->
@if($this->getTableRowCount() > $perPage)
    <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 border-t border-gray-200 dark:border-gray-600">
        <div class="flex items-center justify-between">
            <!-- Results Info -->
            <div class="flex items-center text-sm text-gray-700 dark:text-gray-300">
                <span class="font-medium">
                    {{ __('Showing :from to :to of :total results', [
                        'from' => number_format((($this->getPage() - 1) * $perPage) + 1),
                        'to' => number_format(min($this->getPage() * $perPage, $this->getTableRowCount())),
                        'total' => number_format($this->getTableRowCount())
                    ]) }}
                </span>
            </div>
            
            <!-- Pagination Controls -->
            <div class="flex items-center space-x-2">
                <!-- Previous Button -->
                @if($this->getPage() > 1)
                    <button wire:click="previousPage" 
                            wire:loading.attr="disabled"
                            class="relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:z-10 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                        <x-heroicon-o-chevron-left class="w-4 h-4 mr-1" />
                        {{ __('Previous') }}
                    </button>
                @endif
                
                <!-- Page Numbers -->
                <div class="flex items-center space-x-1">
                    @php
                        $start = max(1, $this->getPage() - 2);
                        $end = min($this->getTotalPages(), $this->getPage() + 2);
                    @endphp
                    
                    @if($start > 1)
                        <button wire:click="gotoPage(1)"
                                class="relative inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:z-10 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                            1
                        </button>
                        @if($start > 2)
                            <span class="px-2 text-gray-500 dark:text-gray-400">...</span>
                        @endif
                    @endif
                    
                    @for($i = $start; $i <= $end; $i++)
                        <button wire:click="gotoPage({{ $i }})"
                                class="relative inline-flex items-center px-3 py-2 border text-sm font-medium rounded-lg focus:z-10 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors duration-200 {{ $i === $this->getPage() ? 'border-blue-500 bg-blue-500 text-white' : 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                            {{ $i }}
                        </button>
                    @endfor
                    
                    @if($end < $this->getTotalPages())
                        @if($end < $this->getTotalPages() - 1)
                            <span class="px-2 text-gray-500 dark:text-gray-400">...</span>
                        @endif
                        <button wire:click="gotoPage({{ $this->getTotalPages() }})"
                                class="relative inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:z-10 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                            {{ $this->getTotalPages() }}
                        </button>
                    @endif
                </div>
                
                <!-- Next Button -->
                @if($this->getPage() < $this->getTotalPages())
                    <button wire:click="nextPage" 
                            wire:loading.attr="disabled"
                            class="relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:z-10 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                        {{ __('Next') }}
                        <x-heroicon-o-chevron-right class="w-4 h-4 ml-1" />
                    </button>
                @endif
            </div>
        </div>
    </div>
@endif