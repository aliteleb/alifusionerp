<x-filament-panels::page>
    {{-- Custom Description Modal (No Livewire) --}}
    <div 
        x-data="{
            showModal: false,
            loading: false,
            taskId: null,
            taskName: '',
            content: '',
            openModal(taskId, taskName) {
                console.log('Opening modal for task:', taskId, taskName);
                this.taskId = taskId;
                this.taskName = taskName;
                this.showModal = true;
                this.loading = true;
                this.content = '';
                
                fetch('/tasks/' + taskId + '/description')
                    .then(response => {
                        console.log('Response received:', response.status);
                        return response.text();
                    })
                    .then(html => {
                        console.log('HTML loaded, length:', html.length);
                        this.content = html;
                        this.loading = false;
                    })
                    .catch(error => {
                        console.error('Error loading description:', error);
                        this.content = '<div class=\'text-danger-600 dark:text-danger-400\'>{{ __("Error loading description") }}</div>';
                        this.loading = false;
                    });
            }
        }"
        @open-description-modal.window="openModal($event.detail.taskId, $event.detail.taskName)"
    >
        <div 
            x-show="showModal"
            x-cloak
            class="fixed inset-0 z-[100] flex items-center justify-center p-4"
            style="display: none;"
            @keydown.escape.window="showModal = false"
        >
            {{-- Backdrop --}}
            <div 
                @click="showModal = false"
                class="fixed inset-0 bg-gray-500/50 dark:bg-gray-900/75"
            ></div>

            {{-- Modal --}}
            <div 
                x-show="showModal"
                class="relative w-full max-w-3xl max-h-[90vh] bg-white dark:bg-gray-800 rounded-lg shadow-lg z-[101]"
                @click.stop
                dir="auto"
            >
                {{-- Header --}}
                <div class="flex items-center justify-between gap-3 px-3 py-2 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white truncate flex-1" x-text="taskName" dir="auto"></h3>
                    <button 
                        @click="showModal = false"
                        type="button"
                        class="flex-shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                    >
                        <x-heroicon-o-x-mark class="h-4 w-4" />
                    </button>
                </div>

                {{-- Content --}}
                <div class="overflow-y-auto max-h-[calc(90vh-100px)] px-3 py-3">
                    {{-- Loading State --}}
                    <div x-show="loading" class="flex items-center justify-center py-6">
                        <div class="animate-spin rounded-full h-6 w-6 border-2 border-gray-300 border-t-primary-600 dark:border-gray-600 dark:border-t-primary-400"></div>
                    </div>
                    
                    {{-- Content --}}
                    <div 
                        x-show="!loading" 
                        x-html="content"
                        class="text-sm text-gray-700 dark:text-gray-300 [&_*]:text-left rtl:[&_*]:text-right"
                    ></div>
                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-end px-3 py-2 border-t border-gray-200 dark:border-gray-700">
                    <button 
                        @click="showModal = false"
                        type="button"
                        class="px-3 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700"
                    >
                        {{ __('Close') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div
        wire:key="table-view"
        @class([
            'fi-kanban-hidden-table absolute -left-[9999px] -top-[9999px] w-px h-px overflow-hidden' => $this->viewMode !== 'table',
        ])
    >
        {{ $this->table }}
    </div>

    <div
        class="space-y-4"
        wire:key="kanban-view"
        @class(['hidden' => $this->viewMode !== 'kanban'])
        @style(['display: none;' => $this->viewMode !== 'kanban'])
        aria-hidden="{{ $this->viewMode !== 'kanban' ? 'true' : 'false' }}"
    >
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-4 pb-3 border-b border-gray-200 dark:border-gray-700">
                <div class="flex flex-row flex-wrap gap-3 items-end">
                    {{-- Filters Container --}}
                    <div class="flex-1 min-w-0 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3 w-full">
                    {{-- Branch Filter with Search --}}
                <div x-data="{ open: false, search: '', branches: @js(\App\Core\Models\Branch::active()->get()->sortBy('name')->map(fn($b) => ['id' => $b->id, 'name' => $b->name])->values()) }">
                    <div class="relative">
                        <x-filament::input.wrapper>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 rtl:left-auto rtl:right-0 flex items-center pl-3 rtl:pl-0 rtl:pr-3 pointer-events-none">
                                    <x-filament::icon icon="heroicon-o-building-office-2" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                                </div>
                                <div class="absolute inset-y-0 right-0 rtl:right-auto rtl:left-0 flex items-center pr-3 rtl:pr-0 rtl:pl-3 pointer-events-none">
                                    <x-filament::icon icon="heroicon-o-chevron-down" class="h-4 w-4 text-gray-400 dark:text-gray-500 transition-transform" x-bind:class="open && 'rotate-180'" />
                                </div>
                                <button type="button" @click="open = !open" class="fi-input block w-full rounded-lg border-gray-300 shadow-sm transition duration-75 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:focus:border-primary-500 sm:text-sm text-left rtl:text-right pl-10 pr-10 py-1.5">
                                    <span :class="$wire.branchId ? '' : 'text-gray-400 dark:text-gray-500'" class="truncate block text-left rtl:text-right" x-text="$wire.branchId ? branches.find(b => b.id == $wire.branchId)?.name : '{{ __('Branch') }}'"></span>
                                </button>
                            </div>
                        </x-filament::input.wrapper>
                        <div x-show="open" @click.away="open = false" x-transition class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 max-h-60 overflow-hidden">
                            <div class="p-1.5 border-b border-gray-200 dark:border-gray-700">
                                <input type="text" x-model="search" placeholder="{{ __('Search...') }}" class="fi-input block w-full rounded-lg border-gray-300 shadow-sm text-sm px-2.5 py-1.5 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200" />
                            </div>
                            <div class="overflow-y-auto max-h-48">
                                <button type="button" @click="$wire.set('branchId', ''); $wire.call('loadKanbanTasks'); open = false" class="w-full text-left rtl:text-right px-2.5 py-1.5 hover:bg-gray-100 dark:hover:bg-gray-700 text-sm" :class="$wire.branchId === '' ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400' : 'text-gray-700 dark:text-gray-300'">
                                    {{ __('All Branches') }}
                                </button>
                                <template x-for="branch in branches.filter(b => b.name.toLowerCase().includes(search.toLowerCase()))" :key="branch.id">
                                    <button type="button" @click="$wire.set('branchId', branch.id); $wire.call('loadKanbanTasks'); open = false" class="w-full text-left rtl:text-right px-2.5 py-1.5 hover:bg-gray-100 dark:hover:bg-gray-700 text-sm" :class="$wire.branchId == branch.id ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400' : 'text-gray-700 dark:text-gray-300'">
                                        <span x-text="branch.name"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Project Filter with Search --}}
                <div x-data="{ open: false, search: '', projects: @js($this->accessibleProjects->values()) }">
                    <div class="relative">
                        <x-filament::input.wrapper>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 rtl:left-auto rtl:right-0 flex items-center pl-3 rtl:pl-0 rtl:pr-3 pointer-events-none">
                                    <x-filament::icon icon="heroicon-o-folder" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                                </div>
                                <div class="absolute inset-y-0 right-0 rtl:right-auto rtl:left-0 flex items-center pr-3 rtl:pr-0 rtl:pl-3 pointer-events-none">
                                    <x-filament::icon icon="heroicon-o-chevron-down" class="h-4 w-4 text-gray-400 dark:text-gray-500 transition-transform" x-bind:class="open && 'rotate-180'" />
                                </div>
                                <button type="button" @click="open = !open" class="fi-input block w-full rounded-lg border-gray-300 shadow-sm transition duration-75 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:focus:border-primary-500 sm:text-sm text-left rtl:text-right pl-10 pr-10 py-1.5">
                                    <span :class="$wire.projectId ? '' : 'text-gray-400 dark:text-gray-500'" class="truncate block text-left rtl:text-right" x-text="$wire.projectId ? projects.find(p => p.id == $wire.projectId)?.name : '{{ __('Project') }}'"></span>
                                </button>
                            </div>
                        </x-filament::input.wrapper>
                        <div x-show="open" @click.away="open = false" x-transition class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 max-h-60 overflow-hidden">
                            <div class="p-1.5 border-b border-gray-200 dark:border-gray-700">
                                <input type="text" x-model="search" placeholder="{{ __('Search...') }}" class="fi-input block w-full rounded-lg border-gray-300 shadow-sm text-sm px-2.5 py-1.5 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200" />
                            </div>
                            <div class="overflow-y-auto max-h-48">
                                <button type="button" @click="$wire.set('projectId', null); $wire.call('loadKanbanTasks'); open = false" class="w-full text-left rtl:text-right px-2.5 py-1.5 hover:bg-gray-100 dark:hover:bg-gray-700 text-sm" :class="!$wire.projectId ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400' : 'text-gray-700 dark:text-gray-300'">
                                    {{ __('All Projects') }}
                                </button>
                                <template x-for="project in projects.filter(p => p.name.toLowerCase().includes(search.toLowerCase()))" :key="project.id">
                                    <button type="button" @click="$wire.set('projectId', project.id); $wire.call('loadKanbanTasks'); open = false" class="w-full text-left rtl:text-right px-2.5 py-1.5 hover:bg-gray-100 dark:hover:bg-gray-700 text-sm" :class="$wire.projectId == project.id ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400' : 'text-gray-700 dark:text-gray-300'">
                                        <span x-text="project.name"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Date From Filter --}}
                <div x-data="{
                        picker: null,
                        init() {
                            const initializePicker = () => {
                                this.picker = new Litepicker({
                                    element: this.$refs.dateFromInput,
                                    format: 'YYYY-MM-DD',
                                    singleMode: true,
                                    dropdowns: {
                                        minYear: 1900,
                                        maxYear: 2100,
                                    },
                                    setup: (picker) => {
                                        picker.on('selected', (date) => {
                                            $wire.set('dateFrom', date ? date.format('YYYY-MM-DD') : null);
                                            $wire.call('loadKanbanTasks');
                                        });
                                    }
                                });

                                if ($wire.dateFrom) {
                                    this.picker.setDate($wire.dateFrom);
                                }
                            };

                            if (window.Litepicker) {
                                initializePicker();
                                return;
                            }

                            window.addEventListener('litepicker:ready', () => initializePicker(), { once: true });
                        }
                     }">
                    <x-filament::input.wrapper>
                        <div class="relative">
                            <input 
                                type="text" 
                                x-ref="dateFromInput"
                                wire:model.live="dateFrom" 
                                placeholder="{{ __('Date From') }}"
                                class="fi-input block w-full rounded-lg border-gray-300 shadow-sm transition duration-75 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 disabled:bg-gray-50 disabled:text-gray-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:focus:border-primary-500 sm:text-sm pl-10 pr-10 py-1.5 cursor-pointer" 
                                readonly
                            />
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <x-filament::icon icon="heroicon-o-calendar-days" class="h-4 w-4 text-gray-400" />
                            </div>
                            @if($this->dateFrom)
                                <button 
                                    type="button"
                                    x-on:click="picker.clear(); $wire.set('dateFrom', null); $wire.call('updatedDateFrom')"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                                    title="{{ __('Clear') }}"
                                >
                                    <x-filament::icon icon="heroicon-o-x-mark" class="h-4 w-4" />
                                </button>
                            @endif
                        </div>
                    </x-filament::input.wrapper>
                </div>

                {{-- Date To Filter --}}
                <div x-data="{
                        picker: null,
                        init() {
                            const initializePicker = () => {
                                this.picker = new Litepicker({
                                    element: this.$refs.dateToInput,
                                    format: 'YYYY-MM-DD',
                                    singleMode: true,
                                    dropdowns: {
                                        minYear: 1900,
                                        maxYear: 2100,
                                    },
                                    setup: (picker) => {
                                        picker.on('selected', (date) => {
                                            $wire.set('dateTo', date ? date.format('YYYY-MM-DD') : null);
                                            $wire.call('loadKanbanTasks');
                                        });
                                    }
                                });

                                if ($wire.dateTo) {
                                    this.picker.setDate($wire.dateTo);
                                }
                            };

                            if (window.Litepicker) {
                                initializePicker();
                                return;
                            }

                            window.addEventListener('litepicker:ready', () => initializePicker(), { once: true });
                        }
                     }">
                    <x-filament::input.wrapper>
                        <div class="relative">
                            <input 
                                type="text" 
                                x-ref="dateToInput"
                                wire:model.live="dateTo" 
                                placeholder="{{ __('Date To') }}"
                                class="fi-input block w-full rounded-lg border-gray-300 shadow-sm transition duration-75 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 disabled:bg-gray-50 disabled:text-gray-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:focus:border-primary-500 sm:text-sm pl-10 pr-10 py-1.5 cursor-pointer" 
                                readonly
                            />
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <x-filament::icon icon="heroicon-o-calendar-days" class="h-4 w-4 text-gray-400" />
                            </div>
                            @if($this->dateTo)
                                <button 
                                    type="button"
                                    x-on:click="picker.clear(); $wire.set('dateTo', null); $wire.call('updatedDateTo')"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                                    title="{{ __('Clear') }}"
                                >
                                    <x-filament::icon icon="heroicon-o-x-mark" class="h-4 w-4" />
                                </button>
                            @endif
                        </div>
                    </x-filament::input.wrapper>
                </div>

                {{-- Limit Filter --}}
                <div x-data="{ open: false, limits: [{value: 50, label: '50'}, {value: 100, label: '100'}, {value: 200, label: '200'}, {value: 500, label: '500'}, {value: 0, label: '{{ __('All') }}'}] }">
                    <div class="relative">
                        <x-filament::input.wrapper>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 rtl:left-auto rtl:right-0 flex items-center pl-3 rtl:pl-0 rtl:pr-3 pointer-events-none">
                                    <x-filament::icon icon="heroicon-o-list-bullet" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                                </div>
                                <div class="absolute inset-y-0 right-0 rtl:right-auto rtl:left-0 flex items-center pr-3 rtl:pr-0 rtl:pl-3 pointer-events-none">
                                    <x-filament::icon icon="heroicon-o-chevron-down" class="h-4 w-4 text-gray-400 dark:text-gray-500 transition-transform" x-bind:class="open && 'rotate-180'" />
                                </div>
                                <button type="button" @click="open = !open" class="fi-input block w-full rounded-lg border-gray-300 shadow-sm transition duration-75 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:focus:border-primary-500 sm:text-sm text-left rtl:text-right pl-10 pr-10 py-1.5">
                                    <span class="truncate block text-left rtl:text-right" x-text="limits.find(l => l.value == $wire.limit)?.label || $wire.limit"></span>
                                </button>
                            </div>
                        </x-filament::input.wrapper>
                        <div x-show="open" @click.away="open = false" x-transition class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 max-h-60 overflow-hidden">
                            <div class="overflow-y-auto max-h-60">
                                <template x-for="limit in limits" :key="limit.value">
                                    <button type="button" @click="$wire.set('limit', limit.value); open = false" class="w-full text-left rtl:text-right px-2.5 py-1.5 hover:bg-gray-100 dark:hover:bg-gray-700 text-sm" :class="$wire.limit == limit.value ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400' : 'text-gray-700 dark:text-gray-300'">
                                        <span x-text="limit.label"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
                </div>

                {{-- Reset Filters Button --}}
                <div class="flex items-end flex-shrink-0">
                    <button 
                        type="button" 
                        wire:click="resetFilters"
                        wire:loading.attr="disabled"
                        wire:target="resetFilters,loadKanbanTasks"
                        @disabled(!$this->hasActiveFilters() || $this->isLoading)
                        class="inline-flex items-center justify-center h-9 w-9 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-white dark:disabled:hover:bg-gray-700"
                        title="{{ __('Reset Filters') }}"
                    >
                        <span wire:loading.remove wire:target="resetFilters,loadKanbanTasks">
                            <x-filament::icon icon="heroicon-o-arrow-path" class="h-4 w-4" />
                        </span>
                        <span wire:loading wire:target="resetFilters,loadKanbanTasks" class="inline-block">
                            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                    </button>
                </div>
            </div>
            </div>

            <div class="overflow-x-auto pb-4">
                <div class="flex w-full"
                 x-data="{
                    expanded: false,
                    draggedTaskId: null,
                    draggedStatus: null,
                    
                    closeAllContextMenus() {
                        this.$dispatch('close-all-menus');
                    },

                    handleDragStart(event, taskId) {
                        this.draggedTaskId = taskId;
                        const taskElement = event.target.closest('[draggable=\'true\']');
                        this.draggedStatus = taskElement.dataset.status;
                        taskElement.style.opacity = '0.5';
                    },

                    handleDragEnd(event) {
                        event.target.style.opacity = '1';
                        this.removeDragOverStyles();
                    },

                    handleDragOver(event) {
                        event.preventDefault();
                        const column = event.currentTarget;
                        if (!column.classList.contains('drag-over')) {
                            column.classList.add('drag-over');
                            column.style.boxShadow = 'inset 0 0 0 2px rgba(59, 130, 246, 0.3)';
                        }
                        const placeholder = column.querySelector('[data-empty-placeholder]');
                        if (placeholder) {
                            placeholder.style.display = 'none';
                        }
                    },

                    handleDragEnter(event) {
                        event.preventDefault();
                    },

                    handleDragLeave(event) {
                        const column = event.currentTarget;
                        if (!column.contains(event.relatedTarget)) {
                            this.removeDragOverStyle(column);
                        }
                    },

                    handleDrop(event, newStatus) {
                        event.preventDefault();
                        const column = event.currentTarget;
                        this.removeDragOverStyle(column);

                        const draggedElement = document.querySelector(`[data-task-id='${this.draggedTaskId}']`);
                        if (!draggedElement) {
                            this.draggedTaskId = null;
                            this.draggedStatus = null;
                            return;
                        }

                        if (this.draggedTaskId && this.draggedStatus !== newStatus) {
                            const originalStatusContainer = draggedElement.closest('[id^=status-]');
                            const targetStatusContainer = document.querySelector(`#status-${newStatus}`);

                            if (!targetStatusContainer) {
                                draggedElement.style.opacity = '1';
                                this.draggedTaskId = null;
                                this.draggedStatus = null;
                                return;
                            }

                            draggedElement.style.opacity = '1';
                            draggedElement.setAttribute('data-status', newStatus);
                            targetStatusContainer.appendChild(draggedElement);

                            this.addLoadingEffect(draggedElement);
                            this.updateCounters();

                            const originalStatus = this.draggedStatus;
                            const taskId = this.draggedTaskId;

                            this.draggedTaskId = null;
                            this.draggedStatus = null;

                            setTimeout(() => {
                                @this.call('updateTaskStatus', taskId, newStatus)
                                    .then(() => this.removeLoadingEffect(draggedElement))
                                    .catch((error) => {
                                        this.removeLoadingEffect(draggedElement);
                                        if (originalStatusContainer && draggedElement) {
                                            originalStatusContainer.appendChild(draggedElement);
                                            draggedElement.setAttribute('data-status', originalStatus);
                                            this.updateCounters();
                                        }
                                        console.error('Failed to update status:', error);
                                    });
                            }, 0);
                        } else {
                            draggedElement.style.opacity = '1';
                            this.draggedTaskId = null;
                            this.draggedStatus = null;
                        }
                    },

                    updateCounters() {
                        document.querySelectorAll('[id^=status-]').forEach(statusContainer => {
                            const statusId = statusContainer.id.replace('status-', '');
                            const count = statusContainer.querySelectorAll('[draggable=\'true\']').length;
                            const counterElement = document.querySelector(`[data-counter-status='${statusId}']`);
                            if (counterElement) {
                                counterElement.textContent = count;
                            }

                            const placeholder = statusContainer.querySelector('[data-empty-placeholder]');
                            if (placeholder) {
                                placeholder.style.display = count === 0 ? '' : 'none';
                            }
                        });
                    },

                    removeDragOverStyles() {
                        document.querySelectorAll('.drag-over').forEach(element => {
                            this.removeDragOverStyle(element);
                        });
                    },

                    removeDragOverStyle(element) {
                        element.classList.remove('drag-over');
                        element.style.boxShadow = '';
                        const isEmpty = element.querySelectorAll('[draggable=\'true\']').length === 0;
                        const placeholder = element.querySelector('[data-empty-placeholder]');
                        if (isEmpty && placeholder) {
                            placeholder.style.display = '';
                        }
                    },

                    addLoadingEffect(element) {
                        element.classList.add('saving-task');
                        element.style.borderColor = 'rgb(59, 130, 246)';
                        element.style.boxShadow = '0 0 0 3px rgba(59, 130, 246, 0.1)';
                    },

                    removeLoadingEffect(element) {
                        element.classList.remove('saving-task');
                        element.style.borderColor = '';
                        element.style.boxShadow = '';
                    }
                 }">
                @foreach($this->getStatuses() as $status)
                    @php
                        $statusTasks = $this->getTasksByStatus($status->value);
                        $bgColorClass = match($status->getColor()) {
                            'success' => 'bg-success-50 dark:bg-success-900/20',
                            'danger' => 'bg-danger-50 dark:bg-danger-900/20',
                            'warning' => 'bg-warning-50 dark:bg-warning-900/20',
                            'info' => 'bg-info-50 dark:bg-info-900/20',
                            'gray' => 'bg-gray-50 dark:bg-gray-800',
                            'secondary' => 'bg-secondary-50 dark:bg-secondary-900/20',
                            default => 'bg-primary-50 dark:bg-primary-900/20',
                        };
                    @endphp
                    <div
                        class="flex-1 min-w-72 p-2 {{ $bgColorClass }}"
                        x-on:drop="handleDrop($event, '{{ $status->value }}')"
                        x-on:dragover.prevent="handleDragOver($event)"
                        x-on:dragenter.prevent="handleDragEnter($event)"
                        x-on:dragleave="handleDragLeave($event)"
                    >
                        <div class="flex items-center justify-between mb-3 pb-2 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center gap-1.5">
                                @php
                                    $statusIcon = $status->getIcon();
                                @endphp
                                @if($statusIcon)
                                    @php
                                        $colorClass = match($status->getColor()) {
                                            'success' => 'text-success-500',
                                            'danger' => 'text-danger-500',
                                            'warning' => 'text-warning-500',
                                            'info' => 'text-info-500',
                                            'gray' => 'text-gray-500',
                                            'secondary' => 'text-secondary-500',
                                            default => 'text-primary-500',
                                        };
                                    @endphp
                                    <x-filament::icon
                                        :icon="$statusIcon"
                                        class="h-4 w-4 {{ $colorClass }}"
                                    />
                                @endif
                                <h3 class="font-semibold text-gray-900 dark:text-gray-100">
                                    {{ $status->getLabel() }}
                                </h3>
                            </div>
                            @php
                                $badgeBgClass = match($status->getColor()) {
                                    'success' => 'bg-success-100 text-success-700 dark:bg-success-900 dark:text-success-300',
                                    'danger' => 'bg-danger-100 text-danger-700 dark:bg-danger-900 dark:text-danger-300',
                                    'warning' => 'bg-warning-100 text-warning-700 dark:bg-warning-900 dark:text-warning-300',
                                    'info' => 'bg-info-100 text-info-700 dark:bg-info-900 dark:text-info-300',
                                    'gray' => 'bg-gray-100 text-gray-700 dark:bg-gray-900 dark:text-gray-300',
                                    'secondary' => 'bg-secondary-100 text-secondary-700 dark:bg-secondary-900 dark:text-secondary-300',
                                    default => 'bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-300',
                                };
                            @endphp
                            <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badgeBgClass }}" data-counter-status="{{ $status->value }}">
                                {{ $statusTasks->count() }}
                            </span>
                        </div>

                        <div class="space-y-2 min-h-[500px]" id="status-{{ $status->value }}">
                            @foreach($statusTasks as $task)
                                <div
                                    wire:key="task-{{ $task->id }}"
                                    draggable="true"
                                    data-task-id="{{ $task->id }}"
                                    data-status="{{ $task->status->value }}"
                                    wire:loading.class="saving-task"
                                    wire:target="deleteTask"
                                    wire:loading.style="border-color: rgb(59, 130, 246) !important; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;"
                                    x-data="{ 
                                        contextMenuOpen: false,
                                        priorityMenuOpen: false,
                                        progressMenuOpen: false
                                    }"
                                    x-init="
                                        const taskCard = $el;
                                        if (window.Livewire) {
                                            Livewire.on('task-priority-updated', (data) => {
                                                if (data.taskId == {{ $task->id }}) {
                                                    taskCard.classList.remove('saving-task');
                                                    taskCard.style.borderColor = '';
                                                    taskCard.style.boxShadow = '';
                                                    
                                                    // Update priority badge
                                                    if (data.priority !== undefined) {
                                                        const priorityBadge = document.querySelector(`[data-task-priority-badge='{{ $task->id }}']`);
                                                        if (priorityBadge) {
                                                            const priorities = {
                                                                'low': { label: '{{ __('Low') }}', colorClass: 'bg-gray-100 text-gray-700 dark:bg-gray-900 dark:text-gray-300' },
                                                                'medium': { label: '{{ __('Medium') }}', colorClass: 'bg-info-100 text-info-700 dark:bg-info-900 dark:text-info-300' },
                                                                'high': { label: '{{ __('High') }}', colorClass: 'bg-warning-100 text-warning-700 dark:bg-warning-900 dark:text-warning-300' },
                                                                'urgent': { label: '{{ __('Urgent') }}', colorClass: 'bg-danger-100 text-danger-700 dark:bg-danger-900 dark:text-danger-300' }
                                                            };
                                                            
                                                            const priority = priorities[data.priority];
                                                            if (priority) {
                                                                priorityBadge.textContent = priority.label;
                                                                priorityBadge.className = `inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium ${priority.colorClass}`;
                                                            }
                                                        }
                                                    }
                                                }
                                            });
                                            Livewire.on('task-progress-updated', (data) => {
                                                if (data.taskId == {{ $task->id }}) {
                                                    taskCard.classList.remove('saving-task');
                                                    taskCard.style.borderColor = '';
                                                    taskCard.style.boxShadow = '';
                                                    
                                                    // Update progress text
                                                    const progressText = document.querySelector(`[data-task-progress-text='{{ $task->id }}']`);
                                                    if (progressText && data.progress !== undefined) {
                                                        const progressLabel = '{{ __('Progress: :value%', ['value' => '__VALUE__']) }}';
                                                        progressText.textContent = progressLabel.replace('__VALUE__', data.progress);
                                                    }
                                                    
                                                    // Update progress bar
                                                    const progressBar = document.querySelector(`[data-task-progress-bar='{{ $task->id }}']`);
                                                    const progressBarContainer = document.querySelector(`[data-task-progress-bar-container='{{ $task->id }}']`);
                                                    if (progressBar && data.progress !== undefined) {
                                                        progressBar.style.width = Math.min(100, data.progress) + '%';
                                                        if (progressBarContainer) {
                                                            progressBarContainer.style.display = data.progress > 0 ? 'block' : 'none';
                                                        }
                                                    }
                                                }
                                            });
                                        } else {
                                            document.addEventListener('livewire:init', () => {
                                                Livewire.on('task-priority-updated', (data) => {
                                                    if (data.taskId == {{ $task->id }}) {
                                                        taskCard.classList.remove('saving-task');
                                                        taskCard.style.borderColor = '';
                                                        taskCard.style.boxShadow = '';
                                                        
                                                        // Update priority badge
                                                        if (data.priority !== undefined) {
                                                            const priorityBadge = document.querySelector(`[data-task-priority-badge='{{ $task->id }}']`);
                                                            if (priorityBadge) {
                                                                const priorities = {
                                                                    'low': { label: '{{ __('Low') }}', colorClass: 'bg-gray-100 text-gray-700 dark:bg-gray-900 dark:text-gray-300' },
                                                                    'medium': { label: '{{ __('Medium') }}', colorClass: 'bg-info-100 text-info-700 dark:bg-info-900 dark:text-info-300' },
                                                                    'high': { label: '{{ __('High') }}', colorClass: 'bg-warning-100 text-warning-700 dark:bg-warning-900 dark:text-warning-300' },
                                                                    'urgent': { label: '{{ __('Urgent') }}', colorClass: 'bg-danger-100 text-danger-700 dark:bg-danger-900 dark:text-danger-300' }
                                                                };
                                                                
                                                                const priority = priorities[data.priority];
                                                                if (priority) {
                                                                    priorityBadge.textContent = priority.label;
                                                                    priorityBadge.className = `inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium ${priority.colorClass}`;
                                                                }
                                                            }
                                                        }
                                                    }
                                                });
                                                Livewire.on('task-progress-updated', (data) => {
                                                    if (data.taskId == {{ $task->id }}) {
                                                        taskCard.classList.remove('saving-task');
                                                        taskCard.style.borderColor = '';
                                                        taskCard.style.boxShadow = '';
                                                        
                                                        // Update progress text
                                                        const progressText = document.querySelector(`[data-task-progress-text='{{ $task->id }}']`);
                                                        if (progressText && data.progress !== undefined) {
                                                            const progressLabel = '{{ __('Progress: :value%', ['value' => '__VALUE__']) }}';
                                                            progressText.textContent = progressLabel.replace('__VALUE__', data.progress);
                                                        }
                                                        
                                                        // Update progress bar
                                                        const progressBar = document.querySelector(`[data-task-progress-bar='{{ $task->id }}']`);
                                                        const progressBarContainer = document.querySelector(`[data-task-progress-bar-container='{{ $task->id }}']`);
                                                        if (progressBar && data.progress !== undefined) {
                                                            progressBar.style.width = Math.min(100, data.progress) + '%';
                                                            if (progressBarContainer) {
                                                                progressBarContainer.style.display = data.progress > 0 ? 'block' : 'none';
                                                            }
                                                        }
                                                    }
                                                });
                                            });
                                            
                                        }
                                    "
                                    x-on:dragstart="handleDragStart($event, {{ $task->id }})"
                                    x-on:dragend="handleDragEnd($event)"
                                    x-on:close-all-menus.window="contextMenuOpen = false; priorityMenuOpen = false; progressMenuOpen = false"
                                    x-on:click.away="contextMenuOpen = false; priorityMenuOpen = false; progressMenuOpen = false"
                                    @click="
                                        if (!$event.target.closest('.menu-button') && !$event.target.closest('.context-menu') && !$event.target.closest('.priority-menu') && !$event.target.closest('.progress-menu')) {
                                            contextMenuOpen = false;
                                            priorityMenuOpen = false;
                                            progressMenuOpen = false;
                                        }
                                    "
                                    class="relative bg-white dark:bg-gray-900 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 cursor-move hover:shadow-md transition-shadow overflow-visible"
                                >
                                    <div class="absolute top-1.5 left-1 right-1 z-10 flex items-center justify-between gap-0.5">
                                        @if($task->due_date)
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-warning-100 text-warning-700 dark:bg-warning-900 dark:text-warning-300">
                                                {{ $task->due_date->translatedFormat('M d, Y') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                                                {{ __('No Date') }}
                                            </span>
                                        @endif

                                        <span class="text-xs text-gray-600 dark:text-gray-400 font-medium" data-task-progress-text="{{ $task->id }}">
                                            {{ __('Progress: :value%', ['value' => $task->progress ?? 0]) }}
                                        </span>

                                        @if($task->priority)
                                            @php
                                                $priorityBadgeClass = match($task->priority->getColor()) {
                                                    'success' => 'bg-success-100 text-success-700 dark:bg-success-900 dark:text-success-300',
                                                    'danger' => 'bg-danger-100 text-danger-700 dark:bg-danger-900 dark:text-danger-300',
                                                    'warning' => 'bg-warning-100 text-warning-700 dark:bg-warning-900 dark:text-warning-300',
                                                    'info' => 'bg-info-100 text-info-700 dark:bg-info-900 dark:text-info-300',
                                                    'gray' => 'bg-gray-100 text-gray-700 dark:bg-gray-900 dark:text-gray-300',
                                                    default => 'bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-300',
                                                };
                                            @endphp
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium {{ $priorityBadgeClass }}" data-task-priority-badge="{{ $task->id }}">
                                                {{ $task->priority->getLabel() }}
                                            </span>
                                        @else
                                            <span data-task-priority-badge="{{ $task->id }}"></span>
                                        @endif
                                        
                                        <button 
                                            type="button"
                                            @click.stop="
                                                if (!contextMenuOpen) {
                                                    $dispatch('close-all-menus');
                                                    contextMenuOpen = true;
                                                } else {
                                                    contextMenuOpen = false;
                                                }
                                            "
                                            class="menu-button flex-shrink-0 p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors rounded hover:bg-gray-100 dark:hover:bg-gray-800"
                                            title="{{ __('Menu') }}"
                                        >
                                            <x-filament::icon icon="heroicon-o-ellipsis-vertical" class="h-4 w-4" />
                                        </button>
                                    </div>

                                    <div class="px-1.5 py-2.5 pt-8">
                                        <div class="flex items-start justify-between gap-2">
                                            <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-1 text-sm line-clamp-2 flex-1">
                                                {{ $task->name }}
                                            </h4>
                                            <button 
                                                type="button"
                                                @click.stop="expanded = !expanded"
                                                class="flex-shrink-0 p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
                                                x-bind:title="expanded ? '{{ __('Collapse') }}' : '{{ __('Expand') }}'"
                                            >
                                                <x-filament::icon 
                                                    icon="heroicon-o-chevron-down" 
                                                    class="h-4 w-4 transition-transform"
                                                    x-bind:class="expanded && 'rotate-180'"
                                                />
                                            </button>
                                        </div>

                                        <div class="space-y-1 text-xs" x-show="expanded" x-transition>
                                            @if($task->project)
                                                <div class="flex items-center gap-1 text-gray-600 dark:text-gray-400">
                                                    <x-heroicon-o-folder class="h-3 w-3 flex-shrink-0" />
                                                    <span class="truncate">{{ $task->project->name }}</span>
                                                </div>
                                            @endif

                                            @if($task->category)
                                                <div class="flex items-center gap-1 text-gray-600 dark:text-gray-400">
                                                    <x-heroicon-o-tag class="h-3 w-3 flex-shrink-0" />
                                                    <span class="truncate">{{ $task->category->name }}</span>
                                                </div>
                                            @endif

                                            @if($task->assignedTo)
                                                <div class="flex items-center gap-1 text-gray-600 dark:text-gray-400">
                                                    <x-heroicon-o-user class="h-3 w-3 flex-shrink-0" />
                                                    <span class="truncate">{{ $task->assignedTo->name }}</span>
                                                </div>
                                            @endif

                                            @if($task->updated_at)
                                                <div class="flex items-center gap-1 text-gray-500 dark:text-gray-500 pt-1 border-t border-gray-200 dark:border-gray-700">
                                                    <x-heroicon-o-clock class="h-3 w-3 flex-shrink-0" />
                                                    <span class="truncate">{{ __('Last update: :date', ['date' => $task->updated_at->translatedFormat('M d, Y H:i')]) }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="absolute bottom-0 left-0 right-0 h-1 bg-gray-200 dark:bg-gray-700" data-task-progress-bar-container="{{ $task->id }}" style="display: {{ ($task->progress ?? 0) > 0 ? 'block' : 'none' }};">
                                        <div class="h-full bg-primary-500 transition-all duration-300" data-task-progress-bar="{{ $task->id }}" style="width: {{ min(100, $task->progress ?? 0) }}%"></div>
                                    </div>

                                    {{-- Context Menu --}}
                                    <div 
                                        data-context-menu
                                        x-show="contextMenuOpen"
                                        x-cloak
                                        x-transition:enter="transition ease-out duration-100"
                                        x-transition:enter-start="opacity-0 scale-95"
                                        x-transition:enter-end="opacity-100 scale-100"
                                        x-transition:leave="transition ease-in duration-75"
                                        x-transition:leave-start="opacity-100 scale-100"
                                        x-transition:leave-end="opacity-0 scale-95"
                                        class="context-menu absolute top-8 right-1 rtl:right-auto rtl:left-1 z-50 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-1 min-w-[180px]"
                                        @click.stop
                                    >
                                        <a 
                                            href="{{ route('filament.admin.resources.tasks.edit', $task) }}"
                                            @click="contextMenuOpen = false"
                                            class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                        >
                                            <x-filament::icon icon="heroicon-o-pencil" class="h-4 w-4" />
                                            <span>{{ __('Edit') }}</span>
                                        </a>
                                        
                                        <button 
                                            type="button"
                                            @click="
                                                console.log('Description button clicked for task {{ $task->id }}');
                                                contextMenuOpen = false;
                                                $dispatch('open-description-modal', { taskId: {{ $task->id }}, taskName: '{{ addslashes($task->name) }}' });
                                            "
                                            class="w-full flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                        >
                                            <x-filament::icon icon="heroicon-o-document-magnifying-glass" class="h-4 w-4" />
                                            <span>{{ __('Description') }}</span>
                                        </button>
                                        
                                        <div class="relative">
                                            <button 
                                                type="button"
                                                @click="
                                                    progressMenuOpen = false;
                                                    priorityMenuOpen = !priorityMenuOpen;
                                                "
                                                class="w-full flex items-center justify-between gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                            >
                                                <div class="flex items-center gap-2">
                                                    <x-filament::icon icon="heroicon-o-flag" class="h-4 w-4" />
                                                    <span>{{ __('Priority') }}</span>
                                                </div>
                                                <x-filament::icon icon="heroicon-o-chevron-right" class="h-4 w-4 rtl:scale-x-[-1]" />
                                            </button>
                                            
                                            <div 
                                                x-show="priorityMenuOpen"
                                                x-transition
                                                class="priority-menu absolute left-full rtl:left-auto rtl:right-full top-0 ml-1 rtl:ml-0 rtl:mr-1 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-1 min-w-[150px] z-50"
                                                @click.stop
                                            >
                                                @foreach(\App\Core\Enums\TaskPriority::cases() as $priority)
                                                    <button 
                                                        type="button"
                                                        wire:click="updateTaskPriority({{ $task->id }}, '{{ $priority->value }}')"
                                                        @click="
                                                            contextMenuOpen = false; 
                                                            priorityMenuOpen = false;
                                                            const card = $el.closest('[data-task-id=\'{{ $task->id }}\']');
                                                            if (card) {
                                                                card.classList.add('saving-task');
                                                                card.style.borderColor = 'rgb(59, 130, 246)';
                                                                card.style.boxShadow = '0 0 0 3px rgba(59, 130, 246, 0.1)';
                                                            }
                                                        "
                                                        class="w-full flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                                    >
                                                        @php
                                                            $priorityColorClass = match($priority->getColor()) {
                                                                'success' => 'text-success-500',
                                                                'danger' => 'text-danger-500',
                                                                'warning' => 'text-warning-500',
                                                                'info' => 'text-info-500',
                                                                'gray' => 'text-gray-500',
                                                                default => 'text-primary-500',
                                                            };
                                                        @endphp
                                                        <x-filament::icon 
                                                            :icon="$priority->getIcon()" 
                                                            class="h-4 w-4 {{ $priorityColorClass }}"
                                                        />
                                                        <span>{{ $priority->getLabel() }}</span>
                                                        @if($task->priority?->value === $priority->value)
                                                            <x-filament::icon icon="heroicon-o-check" class="h-4 w-4 ml-auto text-primary-500" />
                                                        @endif
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>
                                        
                                        <div class="relative">
                                            <button 
                                                type="button"
                                                @click="
                                                    priorityMenuOpen = false;
                                                    progressMenuOpen = !progressMenuOpen;
                                                "
                                                class="w-full flex items-center justify-between gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                            >
                                                <div class="flex items-center gap-2">
                                                    <x-filament::icon icon="heroicon-o-chart-bar" class="h-4 w-4" />
                                                    <span>{{ __('Progress') }}</span>
                                                </div>
                                                <x-filament::icon icon="heroicon-o-chevron-right" class="h-4 w-4 rtl:scale-x-[-1]" />
                                            </button>
                                            
                                            <div 
                                                x-show="progressMenuOpen"
                                                x-transition
                                                class="progress-menu absolute left-full rtl:left-auto rtl:right-full top-0 ml-1 rtl:ml-0 rtl:mr-1 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-1 min-w-[150px] z-50"
                                                @click.stop
                                            >
                                                @php
                                                    $progressValues = [5, 15, 30, 45, 60, 75, 90, 100];
                                                @endphp
                                                @foreach($progressValues as $progressValue)
                                                    <button 
                                                        type="button"
                                                        wire:click="updateTaskProgress({{ $task->id }}, {{ $progressValue }})"
                                                        @click="
                                                            contextMenuOpen = false; 
                                                            progressMenuOpen = false;
                                                            const card = $el.closest('[data-task-id=\'{{ $task->id }}\']');
                                                            if (card) {
                                                                card.classList.add('saving-task');
                                                                card.style.borderColor = 'rgb(59, 130, 246)';
                                                                card.style.boxShadow = '0 0 0 3px rgba(59, 130, 246, 0.1)';
                                                            }
                                                        "
                                                        class="w-full flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                                    >
                                                        <x-filament::icon icon="heroicon-o-chart-bar" class="h-4 w-4 text-primary-500" />
                                                        <span>{{ $progressValue }}%</span>
                                                        @if(($task->progress ?? 0) == $progressValue)
                                                            <x-filament::icon icon="heroicon-o-check" class="h-4 w-4 ml-auto text-primary-500" />
                                                        @endif
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>
                                        
                                        <button 
                                            type="button"
                                            wire:click="deleteTask({{ $task->id }})"
                                            wire:confirm="{{ __('Are you sure you want to delete this task?') }}"
                                            @click="contextMenuOpen = false"
                                            class="w-full flex items-center gap-2 px-4 py-2 text-sm text-danger-600 dark:text-danger-400 hover:bg-danger-50 dark:hover:bg-danger-900/20 transition-colors"
                                        >
                                            <x-filament::icon icon="heroicon-o-trash" class="h-4 w-4" />
                                            <span>{{ __('Delete') }}</span>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                            <div class="text-center py-6 text-gray-500 dark:text-gray-400 text-sm" data-empty-placeholder style="display: {{ $statusTasks->count() === 0 ? '' : 'none' }};">
                                {{ __('No tasks') }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        .saving-task {
            position: relative;
            overflow: hidden;
        }

        .saving-task::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                90deg,
                transparent 0%,
                rgba(59, 130, 246, 0.18) 50%,
                transparent 100%
            );
            animation: shimmer-task 1.5s infinite;
            z-index: 1;
            pointer-events: none;
        }

        @keyframes shimmer-task {
            0% {
                left: -100%;
            }
            100% {
                left: 100%;
            }
        }

        .dark .saving-task::before {
            background: linear-gradient(
                90deg,
                transparent 0%,
                rgba(59, 130, 246, 0.25) 50%,
                transparent 100%
            );
        }
    </style>
    @endpush

    @pushOnce('styles', 'litepicker-styles')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/litepicker/dist/css/litepicker.css">
        <style>
            .litepicker {
                background: white !important;
                border: 1px solid rgb(229, 231, 235) !important;
                border-radius: 0.5rem;
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
                color: rgb(17, 24, 39) !important;
            }
            
            .dark .litepicker,
            .dark .litepicker * {
                background: rgb(31, 41, 55) !important;
                border-color: rgb(55, 65, 81) !important;
                color: rgb(243, 244, 246) !important;
            }
            
            .litepicker .month-item-name,
            .litepicker .month-item-year {
                color: rgb(17, 24, 39) !important;
            }
            
            .dark .litepicker .month-item-name,
            .dark .litepicker .month-item-year {
                color: rgb(243, 244, 246) !important;
            }
            
            .litepicker .day-item {
                color: rgb(17, 24, 39) !important;
            }
            
            .dark .litepicker .day-item {
                color: rgb(243, 244, 246) !important;
            }
            
            .litepicker .day-item.is-selected {
                background: rgb(59, 130, 246) !important;
                color: white !important;
            }
            
            .litepicker .day-item:hover {
                background: rgb(239, 246, 255) !important;
            }
            
            .dark .litepicker .day-item:hover {
                background: rgb(55, 65, 81) !important;
                color: rgb(243, 244, 246) !important;
            }
            
            .litepicker .button-previous-month,
            .litepicker .button-next-month {
                color: rgb(17, 24, 39) !important;
            }
            
            .dark .litepicker .button-previous-month,
            .dark .litepicker .button-next-month {
                color: rgb(243, 244, 246) !important;
            }
            
            .litepicker .button-previous-month:hover,
            .litepicker .button-next-month:hover {
                background: rgb(239, 246, 255) !important;
            }
            
            .dark .litepicker .button-previous-month:hover,
            .dark .litepicker .button-next-month:hover {
                background: rgb(55, 65, 81) !important;
            }
            
            .litepicker .month-item-weekdays-row > div {
                color: rgb(107, 114, 128) !important;
            }
            
            .dark .litepicker .month-item-weekdays-row > div {
                color: rgb(156, 163, 175) !important;
            }
            
            .litepicker .day-item.is-today {
                border-color: rgb(59, 130, 246) !important;
            }
            
            .dark .litepicker .day-item.is-today {
                border-color: rgb(59, 130, 246) !important;
            }
        </style>
    @endPushOnce

    @pushOnce('scripts', 'litepicker-loader')
        <script src="https://cdn.jsdelivr.net/npm/litepicker/dist/litepicker.js" data-litepicker-script></script>
        <script>
            (function () {
                const dispatchReadyEvent = () => {
                    if (window.__litepickerReadyDispatched) {
                        return;
                    }

                    window.__litepickerReadyDispatched = true;
                    window.dispatchEvent(new CustomEvent('litepicker:ready'));
                };

                const script = document.querySelector('script[data-litepicker-script]');

                if (!script) {
                    return;
                }

                if (window.Litepicker) {
                    dispatchReadyEvent();
                    return;
                }

                script.addEventListener('load', () => dispatchReadyEvent(), { once: true });
            })();
        </script>
    @endPushOnce

    @pushOnce('scripts', 'litepicker-theme-watcher')
        <script>
            // Watch for dark mode changes and update Litepicker styles
            document.addEventListener('DOMContentLoaded', function() {
                const observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                            // Force re-render of any open Litepicker instances
                            const pickers = document.querySelectorAll('.litepicker');
                            pickers.forEach(function(picker) {
                                if (picker.style.display !== 'none') {
                                    // Trigger a repaint by toggling visibility
                                    const currentDisplay = picker.style.display;
                                    picker.style.display = 'none';
                                    setTimeout(function() {
                                        picker.style.display = currentDisplay;
                                    }, 10);
                                }
                            });
                        }
                    });
                });
                
                observer.observe(document.documentElement, {
                    attributes: true,
                    attributeFilter: ['class']
                });
            });

            // Prevent task reordering when modal closes
            document.addEventListener('livewire:init', () => {
                let isModalClosing = false;
                let skipNextMorph = false;
                let skipNextUpdate = false;

                // Get Livewire component instance
                const getComponent = () => {
                    const componentElement = document.querySelector('[wire\\:id]');
                    if (componentElement && window.Livewire) {
                        const componentId = componentElement.getAttribute('wire:id');
                        return Livewire.find(componentId);
                    }
                    return null;
                };

                // Hook into Livewire's morph process to prevent reordering when modal is closing
                Livewire.hook('morph', ({ component, el, skip }) => {
                    if (skipNextMorph || isModalClosing) {
                        skipNextMorph = false;
                        return skip();
                    }
                });

                // Hook into Livewire's request process to prevent update calls after modal close
                Livewire.hook('request', ({ uri, options, payload, respond, succeed, fail }) => {
                    // Check if modal is closing and this is an update request
                    if (skipNextUpdate || isModalClosing) {
                        // Check if this is a component update request (has updates or serverMemo)
                        const hasUpdates = payload && (
                            (payload.updates && payload.updates.length > 0) ||
                            payload.serverMemo ||
                            (payload.fingerprint && payload.fingerprint.name && payload.fingerprint.name.includes('list-tasks'))
                        );
                        
                        if (hasUpdates) {
                            // Prevent the request by returning a mock success response
                            return respond(({ status }) => {
                                return succeed({ snapshot: false });
                            });
                        }
                    }
                });

                // Listen for Livewire event to prevent reordering and updates
                Livewire.on('modal-closed-prevent-reorder', () => {
                    skipNextMorph = true;
                    skipNextUpdate = true;
                    isModalClosing = true;
                    const component = getComponent();
                    if (component && typeof component.skipRender === 'function') {
                        component.skipRender();
                    }
                    setTimeout(() => {
                        isModalClosing = false;
                        skipNextUpdate = false;
                    }, 500);
                });

                // Listen for modal close events via DOM observation
                const modalObserver = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        mutation.removedNodes.forEach(function(removedNode) {
                            if (removedNode.nodeType === 1) {
                                const isModal = removedNode.getAttribute && (
                                    removedNode.getAttribute('role') === 'dialog' ||
                                    (removedNode.querySelector && removedNode.querySelector('[role="dialog"]'))
                                );
                                
                                if (isModal) {
                                    // Modal was closed - prevent re-renders and updates
                                    skipNextMorph = true;
                                    skipNextUpdate = true;
                                    isModalClosing = true;
                                    
                                    const component = getComponent();
                                    if (component && typeof component.skipRender === 'function') {
                                        component.skipRender();
                                    }
                                    
                                    setTimeout(() => {
                                        isModalClosing = false;
                                        skipNextUpdate = false;
                                    }, 500);
                                }
                            }
                        });
                    });
                });

                // Observe document body for modal removal
                modalObserver.observe(document.body, {
                    childList: true,
                    subtree: true
                });

                // Also listen for modal open to remove loading animation immediately
                const modalOpenObserver = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        mutation.addedNodes.forEach(function(addedNode) {
                            if (addedNode.nodeType === 1) {
                                const isModal = addedNode.getAttribute && (
                                    addedNode.getAttribute('role') === 'dialog' ||
                                    (addedNode.querySelector && addedNode.querySelector('[role="dialog"]'))
                                );
                                
                                if (isModal) {
                                    // Modal was opened - remove loading animation after a short delay
                                    setTimeout(() => {
                                        // Remove loading animation from all task cards
                                        document.querySelectorAll('[data-task-id]').forEach(card => {
                                            card.classList.remove('saving-task');
                                            card.style.borderColor = '';
                                            card.style.boxShadow = '';
                                        });
                                    }, 100);
                                }
                            }
                        });
                    });
                });

                // Observe document body for modal addition
                modalOpenObserver.observe(document.body, {
                    childList: true,
                    subtree: true
                });
            });
        </script>
    @endPushOnce

<x-filament-actions::modals />
</x-filament-panels::page>

