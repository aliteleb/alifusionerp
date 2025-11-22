<!-- Table Structure -->
@if(count($tableStructure) > 0)
    <div class="h-full flex flex-col bg-white dark:bg-gray-800 rounded border border-gray-200 dark:border-gray-700 overflow-hidden"
         x-data="{ 
            editingColumn: null, 
            editingData: {},
            showAddColumn: false,
            reorderMode: false,
            draggedIndex: null,
            columnOrder: @js(array_column($tableStructure, 'column_name')),
            newColumn: {
                column_name: '',
                data_type: 'character varying',
                is_nullable: 'YES',
                column_default: '',
                character_maximum_length: null
            },
            startEdit(columnName, column) {
                this.editingColumn = columnName;
                this.editingData = {
                    data_type: column.data_type,
                    is_nullable: column.is_nullable,
                    column_default: column.column_default,
                    character_maximum_length: column.character_maximum_length
                };
            },
            cancelEdit() {
                this.editingColumn = null;
                this.editingData = {};
            },
            resetNewColumn() {
                this.newColumn = {
                    column_name: '',
                    data_type: 'character varying',
                    is_nullable: 'YES',
                    column_default: '',
                    character_maximum_length: null
                };
            },
            toggleReorderMode() {
                this.reorderMode = !this.reorderMode;
                if (!this.reorderMode) {
                    // Reset order when exiting reorder mode
                    this.columnOrder = @js(array_column($tableStructure, 'column_name'));
                }
            },
            handleDragStart(index) {
                this.draggedIndex = index;
            },
            handleDragOver(event, index) {
                event.preventDefault();
                if (this.draggedIndex !== null && this.draggedIndex !== index) {
                    const newOrder = [...this.columnOrder];
                    const draggedItem = newOrder[this.draggedIndex];
                    newOrder.splice(this.draggedIndex, 1);
                    newOrder.splice(index, 0, draggedItem);
                    this.columnOrder = newOrder;
                    this.draggedIndex = index;
                }
            },
            handleDragEnd() {
                this.draggedIndex = null;
            },
            getColumnByName(columnName) {
                return @js($tableStructure).find(col => col.column_name === columnName);
            },
            saveColumnOrder() {
                $wire.reorderColumns(this.columnOrder);
            },
            resetState() {
                this.reorderMode = false;
                this.editingColumn = null;
                this.editingData = {};
                this.columnOrder = @js(array_column($tableStructure, 'column_name'));
                this.draggedIndex = null;
            },
            getDataTypeIcon(dataType) {
                const type = dataType.toLowerCase();
                if (type.includes('int') || type.includes('numeric') || type.includes('decimal') || type.includes('real') || type.includes('double')) {
                    return '🔢';
                }
                if (type.includes('boolean') || type.includes('bool')) {
                    return '✓';
                }
                if (type.includes('char') || type.includes('text')) {
                    return '📝';
                }
                if (type.includes('date') || type.includes('time')) {
                    return '📅';
                }
                if (type.includes('json')) {
                    return '{ }';
                }
                if (type.includes('uuid')) {
                    return '🔑';
                }
                if (type.includes('array')) {
                    return '[ ]';
                }
                return '●';
            }
         }"
         @structure-reloaded.window="resetState(); $wire.$refresh();"
         wire:key="structure-{{ $selectedDatabase }}-{{ $selectedTable }}-{{ md5(json_encode($tableStructure)) }}">
        
        <!-- Header with Action Buttons -->
        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('Table Structure') }}</h3>
            <div class="flex items-center gap-2">
                <!-- Reorder Mode Controls -->
                <template x-if="reorderMode">
                    <div class="flex items-center gap-2">
                        <button @click="saveColumnOrder()" 
                                wire:loading.attr="disabled"
                                class="px-3 py-1.5 text-xs bg-green-600 hover:bg-green-700 text-white rounded flex items-center gap-1.5 disabled:opacity-50">
                            <x-heroicon-o-check class="w-3.5 h-3.5" />
                            {{ __('Apply Order') }}
                        </button>
                        <button @click="toggleReorderMode()" 
                                class="px-3 py-1.5 text-xs bg-gray-500 hover:bg-gray-600 text-white rounded flex items-center gap-1.5">
                            <x-heroicon-o-x-mark class="w-3.5 h-3.5" />
                            {{ __('Cancel') }}
                        </button>
                    </div>
                </template>
                
                <!-- Normal Mode Controls -->
                <template x-if="!reorderMode">
                    <div class="flex items-center gap-2">
                        <button @click="toggleReorderMode()" 
                                class="px-3 py-1.5 text-xs bg-purple-600 hover:bg-purple-700 text-white rounded flex items-center gap-1.5">
                            <x-heroicon-o-arrows-up-down class="w-3.5 h-3.5" />
                            {{ __('Reorder Columns') }}
                        </button>
                        <button @click="showAddColumn = !showAddColumn; if(showAddColumn) resetNewColumn();" 
                                class="px-3 py-1.5 text-xs bg-blue-600 hover:bg-blue-700 text-white rounded flex items-center gap-1.5">
                            <x-heroicon-o-plus class="w-3.5 h-3.5" />
                            {{ __('Add Column') }}
                        </button>
                    </div>
                </template>
            </div>
        </div>

        <!-- Add Column Form -->
        <div x-show="showAddColumn" x-cloak class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-blue-50 dark:bg-blue-900/20">
            <div class="grid grid-cols-6 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Column Name') }}</label>
                    <input type="text" x-model="newColumn.column_name" 
                           class="w-full px-2 py-1 text-xs rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100"
                           placeholder="column_name">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Data Type') }}</label>
                    <select x-model="newColumn.data_type" 
                            class="w-full px-2 py-1 text-xs rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100">
                        <option value="integer">🔢 INTEGER</option>
                        <option value="bigint">🔢 BIGINT</option>
                        <option value="smallint">🔢 SMALLINT</option>
                        <option value="numeric">🔢 NUMERIC</option>
                        <option value="character varying">📝 VARCHAR</option>
                        <option value="character">📝 CHAR</option>
                        <option value="text">📝 TEXT</option>
                        <option value="boolean">✓ BOOLEAN</option>
                        <option value="date">📅 DATE</option>
                        <option value="timestamp without time zone">📅 TIMESTAMP</option>
                        <option value="json">{ } JSON</option>
                        <option value="jsonb">{ } JSONB</option>
                        <option value="uuid">🔑 UUID</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Length') }}</label>
                    <input type="number" x-model="newColumn.character_maximum_length" 
                           class="w-full px-2 py-1 text-xs rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100"
                           placeholder="-">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Nullable') }}</label>
                    <select x-model="newColumn.is_nullable" 
                            class="w-full px-2 py-1 text-xs rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100">
                        <option value="YES">✓ {{ __('Yes') }}</option>
                        <option value="NO">✗ {{ __('No') }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Default') }}</label>
                    <input type="text" x-model="newColumn.column_default" 
                           class="w-full px-2 py-1 text-xs rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 font-mono"
                           placeholder="NULL">
                </div>
                <div class="flex items-end gap-2">
                    <button @click="$wire.addColumn(newColumn); showAddColumn = false; resetNewColumn();" 
                            wire:loading.attr="disabled"
                            class="px-3 py-1.5 text-xs bg-green-600 hover:bg-green-700 text-white rounded disabled:opacity-50">
                        {{ __('Add') }}
                    </button>
                    <button @click="showAddColumn = false; resetNewColumn();" 
                            class="px-3 py-1.5 text-xs bg-gray-500 hover:bg-gray-600 text-white rounded">
                        {{ __('Cancel') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="flex-1 overflow-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900 sticky top-0 z-10">
                    <tr>
                        <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase bg-gray-50 dark:bg-gray-900"
                            x-show="reorderMode">
                            {{ __('Order') }}
                        </th>
                        <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase bg-gray-50 dark:bg-gray-900">
                            {{ __('Column') }}
                        </th>
                        <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase bg-gray-50 dark:bg-gray-900">
                            {{ __('Type') }}
                        </th>
                        <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase bg-gray-50 dark:bg-gray-900">
                            {{ __('Nullable') }}
                        </th>
                        <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase bg-gray-50 dark:bg-gray-900">
                            {{ __('Default') }}
                        </th>
                        <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase bg-gray-50 dark:bg-gray-900">
                            {{ __('Length') }}
                        </th>
                        <th scope="col" class="px-4 py-2 text-right text-xs font-medium text-gray-700 dark:text-gray-300 uppercase bg-gray-50 dark:bg-gray-900"
                            x-show="!reorderMode">
                            {{ __('Actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <template x-for="(columnName, index) in reorderMode ? columnOrder : @js(array_column($tableStructure, 'column_name'))" :key="columnName">
                        <tr x-data="{ 
                            column: reorderMode ? getColumnByName(columnName) : @js($tableStructure)[index],
                            isEditing: false 
                        }"
                            x-init="$watch('editingColumn', value => isEditing = (value === columnName))"
                            class="hover:bg-gray-50 dark:hover:bg-gray-900"
                            :class="{ 'cursor-move': reorderMode, 'opacity-50': draggedIndex === index && reorderMode }"
                            :draggable="reorderMode"
                            @dragstart="handleDragStart(index)"
                            @dragover="handleDragOver($event, index)"
                            @dragend="handleDragEnd()">
                            
                            <!-- Drag Handle / Order -->
                            <td class="px-4 py-2 text-sm" x-show="reorderMode" x-cloak>
                                <div class="flex items-center gap-2">
                                    <x-heroicon-o-bars-3 class="w-4 h-4 text-gray-400" />
                                    <span class="text-gray-600 dark:text-gray-400 text-xs" x-text="index + 1"></span>
                                </div>
                            </td>
                            
                            <!-- Column Name -->
                            <td class="px-4 py-2 text-sm">
                                <div class="flex items-center gap-2">
                                    <template x-if="columnName === '{{ $this->getPrimaryKey() }}'">
                                        <x-heroicon-o-key class="w-3 h-3 text-gray-500 dark:text-gray-400" />
                                    </template>
                                    <span class="text-gray-900 dark:text-white font-medium" x-text="column.column_name"></span>
                                    <template x-if="columnName === '{{ $this->getPrimaryKey() }}'">
                                        <span class="px-2 py-0.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 text-xs rounded">
                                            PK
                                        </span>
                                    </template>
                                </div>
                            </td>
                            
                            <!-- Data Type -->
                            <td class="px-4 py-2 text-sm">
                                <div x-show="!isEditing && !reorderMode" class="flex items-center gap-1.5">
                                    <span class="text-xs opacity-70" x-text="getDataTypeIcon(column.data_type)" title="Data Type Icon"></span>
                                    <span class="text-gray-700 dark:text-gray-300 text-xs font-mono" x-text="column.data_type.toUpperCase()"></span>
                                </div>
                                <div x-show="reorderMode && !isEditing" class="flex items-center gap-1.5">
                                    <span class="text-xs opacity-70" x-text="getDataTypeIcon(column.data_type)" title="Data Type Icon"></span>
                                    <span class="text-gray-700 dark:text-gray-300 text-xs font-mono" x-text="column.data_type.toUpperCase()"></span>
                                </div>
                                <div x-show="isEditing" x-cloak>
                                    <select x-model="editingData.data_type" 
                                            class="w-full px-2 py-1 text-xs rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100">
                                        <option value="integer">🔢 INTEGER</option>
                                        <option value="bigint">🔢 BIGINT</option>
                                        <option value="smallint">🔢 SMALLINT</option>
                                        <option value="numeric">🔢 NUMERIC</option>
                                        <option value="real">🔢 REAL</option>
                                        <option value="double precision">🔢 DOUBLE PRECISION</option>
                                        <option value="character varying">📝 VARCHAR</option>
                                        <option value="character">📝 CHAR</option>
                                        <option value="text">📝 TEXT</option>
                                        <option value="boolean">✓ BOOLEAN</option>
                                        <option value="date">📅 DATE</option>
                                        <option value="time without time zone">📅 TIME</option>
                                        <option value="timestamp without time zone">📅 TIMESTAMP</option>
                                        <option value="timestamp with time zone">📅 TIMESTAMPTZ</option>
                                        <option value="json">{ } JSON</option>
                                        <option value="jsonb">{ } JSONB</option>
                                        <option value="uuid">🔑 UUID</option>
                                    </select>
                                </div>
                            </td>
                            
                            <!-- Nullable -->
                            <td class="px-4 py-2 text-sm">
                                <div x-show="!isEditing" class="flex items-center justify-center">
                                    <span class="text-base" 
                                          :class="column.is_nullable === 'YES' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'" 
                                          x-text="column.is_nullable === 'YES' ? '✓' : '✗'"></span>
                                </div>
                                <div x-show="isEditing" x-cloak>
                                    <select x-model="editingData.is_nullable" 
                                            class="w-full px-2 py-1 text-xs rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100">
                                        <option value="YES">✓ {{ __('Yes') }}</option>
                                        <option value="NO">✗ {{ __('No') }}</option>
                                    </select>
                                </div>
                            </td>
                            
                            <!-- Default Value -->
                            <td class="px-4 py-2 text-sm">
                                <div x-show="!isEditing">
                                    <template x-if="column.column_default">
                                        <code class="text-xs text-gray-700 dark:text-gray-300 font-mono" x-text="column.column_default"></code>
                                    </template>
                                    <template x-if="!column.column_default">
                                        <span class="text-xs text-gray-400 italic">-</span>
                                    </template>
                                </div>
                                <div x-show="isEditing" x-cloak>
                                    <input type="text" 
                                           x-model="editingData.column_default"
                                           placeholder="{{ __('NULL') }}"
                                           class="w-full px-2 py-1 text-xs rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 font-mono">
                                </div>
                            </td>
                            
                            <!-- Length -->
                            <td class="px-4 py-2 text-sm">
                                <div x-show="!isEditing">
                                    <template x-if="column.character_maximum_length">
                                        <span class="text-xs text-gray-600 dark:text-gray-400" x-text="Number(column.character_maximum_length).toLocaleString()"></span>
                                    </template>
                                    <template x-if="!column.character_maximum_length">
                                        <span class="text-xs text-gray-400">-</span>
                                    </template>
                                </div>
                                <div x-show="isEditing" x-cloak>
                                    <input type="number" 
                                           x-model="editingData.character_maximum_length"
                                           placeholder="-"
                                           class="w-full px-2 py-1 text-xs rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100">
                                </div>
                            </td>
                            
                            <!-- Actions -->
                            <td class="px-4 py-2 text-sm text-right" x-show="!reorderMode">
                                <div x-show="!isEditing" class="flex items-center justify-end gap-2">
                                    <button @click="startEdit(columnName, column)"
                                            class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                        <x-heroicon-o-pencil class="w-4 h-4" />
                                    </button>
                                    <template x-if="columnName !== '{{ $this->getPrimaryKey() }}'">
                                        <button @click="if(confirm('{{ __('Are you sure you want to delete this column?') }}')) { $wire.deleteColumn(columnName); }"
                                                class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                                            <x-heroicon-o-trash class="w-4 h-4" />
                                        </button>
                                    </template>
                                </div>
                                <div x-show="isEditing" x-cloak class="flex items-center justify-end gap-2">
                                    <button @click="$wire.saveColumnStructure(columnName, editingData); cancelEdit();"
                                            wire:loading.attr="disabled"
                                            class="px-2 py-1 text-xs bg-green-600 hover:bg-green-700 text-white rounded disabled:opacity-50">
                                        {{ __('Save') }}
                                    </button>
                                    <button @click="cancelEdit()"
                                            class="px-2 py-1 text-xs bg-gray-500 hover:bg-gray-600 text-white rounded">
                                        {{ __('Cancel') }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
@else
    <!-- Empty State -->
    <div class="bg-white dark:bg-gray-800 rounded border border-gray-200 dark:border-gray-700 p-12 text-center">
        <x-heroicon-o-cog-6-tooth class="w-12 h-12 text-gray-300 dark:text-gray-600 mx-auto mb-3" />
        <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-1">{{ __('No structure information') }}</h3>
        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Unable to load table structure information.') }}</p>
    </div>
@endif