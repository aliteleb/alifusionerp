<!-- Table Data -->
@if(count($tableData) > 0)
    <div class="h-full" 
         x-data="{ 
            editingRowId: null, 
            editingData: {},
            selectedRows: [],
            toggleAll() {
                if (this.selectedRows.length === {{ count($tableData) }}) {
                    this.selectedRows = [];
                } else {
                    this.selectedRows = [{{ implode(',', array_map(fn($i) => "'$i'", array_keys($tableData))) }}];
                }
            }
        }"
        x-init="
            // Reset state when table data changes
            $watch('$wire.selectedTable', () => {
                editingRowId = null;
                editingData = {};
                selectedRows = [];
            });
        "
        wire:key="table-{{ $selectedDatabase }}-{{ $selectedTable }}"
    >
        <!-- Bulk Actions Bar -->
        <div x-show="selectedRows.length > 0" 
             x-transition
             class="bg-blue-50 dark:bg-blue-900/20 border-b border-blue-200 dark:border-blue-800 px-4 py-2 flex items-center justify-between"
             style="display: none;">
            <div class="flex items-center gap-2 text-sm">
                <x-heroicon-o-check-circle class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                <span class="text-blue-900 dark:text-blue-100 font-medium">
                    <span x-text="selectedRows.length"></span> {{ __('selected') }}
                </span>
            </div>
            <div class="flex items-center gap-2">
                <button @click="selectedRows = []"
                        class="text-xs text-blue-700 dark:text-blue-300 hover:text-blue-900 dark:hover:text-blue-100">
                    {{ __('Clear Selection') }}
                </button>
                <button @click="
                        const confirmed = confirm('{{ __('Are you sure you want to delete') }} ' + selectedRows.length + ' {{ __('rows') }}?');
                        if (confirmed) {
                            $wire.bulkDeleteRows(selectedRows);
                            selectedRows = [];
                        }
                    "
                        class="inline-flex items-center gap-1 px-3 py-1 bg-red-600 hover:bg-red-700 text-white text-xs rounded">
                    <x-heroicon-o-trash class="w-3 h-3" />
                    {{ __('Delete Selected') }}
                </button>
            </div>
        </div>
        
        <!-- Table Container with white background -->
        <div :style="selectedRows.length > 0 ? 'height: calc(100% - 48px)' : 'height: 100%'" 
             class="bg-white dark:bg-gray-800 rounded border border-gray-200 dark:border-gray-700 overflow-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <!-- Table Header - Sticky -->
                <thead class="bg-gray-50 dark:bg-gray-900 sticky top-0 z-10">
                    <tr>
                        <!-- Select All Checkbox -->
                        @if($this->getPrimaryKey())
                            <th scope="col" class="px-4 py-2 text-left bg-gray-50 dark:bg-gray-900 w-12">
                                <input type="checkbox" 
                                       @click="toggleAll()"
                                       :checked="selectedRows.length === {{ count($tableData) }}"
                                       class="rounded border-gray-300 dark:border-gray-700 text-gray-900 focus:ring-gray-500">
                            </th>
                        @endif
                        
                        <!-- Actions Column Header -->
                        <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase bg-gray-50 dark:bg-gray-900">
                            {{ __('Actions') }}
                        </th>
                        <!-- Data Columns -->
                        @foreach(array_keys($tableData[0]) as $column)
                            <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase bg-gray-50 dark:bg-gray-900">
                                <div class="flex items-center gap-1">
                                    <span>{{ $column }}</span>
                                    @if($column === $this->getPrimaryKey())
                                        <x-heroicon-o-key class="w-3 h-3 text-gray-500 dark:text-gray-400" />
                                    @endif
                                </div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                
                <!-- Table Body -->
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($tableData as $index => $row)
                        <tr :class="editingRowId === '{{ $index }}' ? 'bg-gray-100 dark:bg-gray-900' : (selectedRows.includes('{{ $index }}') ? 'bg-blue-50 dark:bg-blue-900/20' : '')" 
                            class="hover:bg-gray-50 dark:hover:bg-gray-900">
                            
                            <!-- Checkbox Column -->
                            @if($this->getPrimaryKey())
                                <td class="px-4 py-2">
                                    <input type="checkbox" 
                                           x-model="selectedRows"
                                           value="{{ $index }}"
                                           class="rounded border-gray-300 dark:border-gray-700 text-gray-900 focus:ring-gray-500">
                                </td>
                            @endif
                            
                            <!-- Actions Column -->
                            <td class="px-4 py-2 text-sm whitespace-nowrap">
                                <!-- Edit Mode Actions -->
                                <div x-show="editingRowId === '{{ $index }}'" class="flex items-center gap-1" style="display: none;">
                                    <button @click="$wire.saveRow('{{ $index }}', editingData); editingRowId = null; editingData = {};" 
                                            title="{{ __('Save') }}"
                                            class="inline-flex items-center p-1.5 bg-gray-900 dark:bg-gray-100 hover:bg-gray-800 dark:hover:bg-gray-200 text-white dark:text-gray-900 rounded">
                                        <x-heroicon-o-check class="w-4 h-4" />
                                    </button>
                                    <button @click="editingRowId = null; editingData = {};" 
                                            title="{{ __('Cancel') }}"
                                            class="inline-flex items-center p-1.5 bg-gray-400 dark:bg-gray-600 hover:bg-gray-500 dark:hover:bg-gray-500 text-white rounded">
                                        <x-heroicon-o-x-mark class="w-4 h-4" />
                                    </button>
                                </div>
                                
                                <!-- View Mode Actions -->
                                <div x-show="editingRowId !== '{{ $index }}'" class="flex items-center gap-1">
                                    <button @click="editingRowId = '{{ $index }}'; editingData = {{ json_encode($row) }};" 
                                            title="{{ __('Edit') }}"
                                            class="inline-flex items-center p-1.5 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded">
                                        <x-heroicon-o-pencil class="w-4 h-4" />
                                    </button>
                                    <button @click="
                                                if (confirm('{{ __('Are you sure you want to delete this row?') }}')) {
                                                    $wire.deleteRow({{ $index }});
                                                }
                                            "
                                            title="{{ __('Delete') }}"
                                            class="inline-flex items-center p-1.5 bg-gray-200 dark:bg-gray-700 hover:bg-red-100 dark:hover:bg-red-900 text-gray-700 dark:text-gray-300 hover:text-red-700 dark:hover:text-red-300 rounded">
                                        <x-heroicon-o-trash class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                            
                            <!-- Data Columns -->
                            @foreach($row as $column => $value)
                                <td class="px-4 py-2 text-sm whitespace-nowrap">
                                    <template x-if="editingRowId === '{{ $index }}' && '{{ $column }}' !== '{{ $this->getPrimaryKey() }}'">
                                        <!-- Edit Mode -->
                                        <input type="text" 
                                               x-model="editingData.{{ $column }}"
                                               class="w-full rounded border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 dark:text-gray-100 text-xs px-2 py-1 focus:border-gray-500 focus:ring-gray-500">
                                    </template>
                                    
                                    <template x-if="editingRowId !== '{{ $index }}' || '{{ $column }}' === '{{ $this->getPrimaryKey() }}'">
                                        <!-- View Mode -->
                                        <div class="flex items-center gap-2">
                                            @if($column === $this->getPrimaryKey())
                                                <x-heroicon-o-key class="w-3 h-3 text-gray-400 dark:text-gray-500" />
                                            @endif
                                            @if(is_null($value))
                                                <span class="text-xs text-gray-400 dark:text-gray-500 italic">NULL</span>
                                            @elseif(is_bool($value))
                                                <span class="text-xs {{ $value ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                                    {{ $value ? 'TRUE' : 'FALSE' }}
                                                </span>
                                            @else
                                                <span class="text-gray-700 dark:text-gray-300 truncate max-w-xs" title="{{ $value }}">
                                                    {{ is_string($value) ? Str::limit($value, 50) : $value }}
                                                </span>
                                            @endif
                                        </div>
                                    </template>
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@else
    <!-- Empty State -->
    <div class="bg-white dark:bg-gray-800 rounded border border-gray-200 dark:border-gray-700 p-12 text-center">
        <x-heroicon-o-inbox class="w-12 h-12 text-gray-300 dark:text-gray-600 mx-auto mb-3" />
        <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-1">
            {{ __('No data found') }}
        </h3>
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
            {{ __('This table appears to be empty.') }}
        </p>
        <button wire:click="refreshTableData" 
                class="inline-flex items-center gap-2 px-3 py-1.5 bg-gray-900 dark:bg-gray-100 hover:bg-gray-800 dark:hover:bg-gray-200 text-white dark:text-gray-900 text-xs rounded">
            <x-heroicon-o-arrow-path class="w-3 h-3" />
            {{ __('Refresh Table') }}
        </button>
    </div>
@endif
