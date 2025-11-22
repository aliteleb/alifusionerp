<x-filament-panels::page>
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
                                <button type="button" @click="$wire.set('branchId', ''); $wire.call('loadKanbanTickets'); open = false" class="w-full text-left rtl:text-right px-2.5 py-1.5 hover:bg-gray-100 dark:hover:bg-gray-700 text-sm" :class="$wire.branchId === '' ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400' : 'text-gray-700 dark:text-gray-300'">
                                    {{ __('All Branches') }}
                                </button>
                                <template x-for="branch in branches.filter(b => b.name.toLowerCase().includes(search.toLowerCase()))" :key="branch.id">
                                    <button type="button" @click="$wire.set('branchId', branch.id); $wire.call('loadKanbanTickets'); open = false" class="w-full text-left rtl:text-right px-2.5 py-1.5 hover:bg-gray-100 dark:hover:bg-gray-700 text-sm" :class="$wire.branchId == branch.id ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400' : 'text-gray-700 dark:text-gray-300'">
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
                                <button type="button" @click="$wire.set('projectId', null); $wire.call('loadKanbanTickets'); open = false" class="w-full text-left rtl:text-right px-2.5 py-1.5 hover:bg-gray-100 dark:hover:bg-gray-700 text-sm" :class="!$wire.projectId ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400' : 'text-gray-700 dark:text-gray-300'">
                                    {{ __('All Projects') }}
                                </button>
                                <template x-for="project in projects.filter(p => p.name.toLowerCase().includes(search.toLowerCase()))" :key="project.id">
                                    <button type="button" @click="$wire.set('projectId', project.id); $wire.call('loadKanbanTickets'); open = false" class="w-full text-left rtl:text-right px-2.5 py-1.5 hover:bg-gray-100 dark:hover:bg-gray-700 text-sm" :class="$wire.projectId == project.id ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400' : 'text-gray-700 dark:text-gray-300'">
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
                                            $wire.call('loadKanbanTickets');
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
                                            $wire.call('loadKanbanTickets');
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
                        wire:target="resetFilters,loadKanbanTickets"
                        @disabled(!$this->hasActiveFilters() || $this->isLoading)
                        class="inline-flex items-center justify-center h-9 w-9 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-white dark:disabled:hover:bg-gray-700"
                        title="{{ __('Reset Filters') }}"
                    >
                        <span wire:loading.remove wire:target="resetFilters,loadKanbanTickets">
                            <x-filament::icon icon="heroicon-o-arrow-path" class="h-4 w-4" />
                        </span>
                        <span wire:loading wire:target="resetFilters,loadKanbanTickets" class="inline-block">
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
                    draggedTicketId: null,
                    draggedStatus: null,
                    
                    closeAllContextMenus() {
                        this.$dispatch('close-all-menus');
                    },

                    handleDragStart(event, ticketId) {
                        this.draggedTicketId = ticketId;
                        const ticketElement = event.target.closest('[draggable=\'true\']');
                        this.draggedStatus = ticketElement.dataset.status;
                        ticketElement.style.opacity = '0.5';
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

                        const draggedElement = document.querySelector(`[data-ticket-id='${this.draggedTicketId}']`);
                        if (!draggedElement) {
                            this.draggedTicketId = null;
                            this.draggedStatus = null;
                            return;
                        }

                        if (this.draggedTicketId && this.draggedStatus !== newStatus) {
                            const originalStatusContainer = draggedElement.closest('[id^=status-]');
                            const targetStatusContainer = document.querySelector(`#status-${newStatus}`);

                            if (!targetStatusContainer) {
                                draggedElement.style.opacity = '1';
                                this.draggedTicketId = null;
                                this.draggedStatus = null;
                                return;
                            }

                            draggedElement.style.opacity = '1';
                            draggedElement.setAttribute('data-status', newStatus);
                            targetStatusContainer.appendChild(draggedElement);

                            this.addLoadingEffect(draggedElement);
                            this.updateCounters();

                            const originalStatus = this.draggedStatus;
                            const ticketId = this.draggedTicketId;

                            this.draggedTicketId = null;
                            this.draggedStatus = null;

                            setTimeout(() => {
                                @this.call('updateTicketStatus', ticketId, newStatus)
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
                            this.draggedTicketId = null;
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
                        element.classList.add('saving-ticket');
                        element.style.borderColor = 'rgb(59, 130, 246)';
                        element.style.boxShadow = '0 0 0 3px rgba(59, 130, 246, 0.1)';
                    },

                    removeLoadingEffect(element) {
                        element.classList.remove('saving-ticket');
                        element.style.borderColor = '';
                        element.style.boxShadow = '';
                    }
                 }">
                @foreach($this->getStatuses() as $status)
                    @php
                        $statusTickets = $this->getTicketsByStatus($status->value);
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
                                {{ $statusTickets->count() }}
                            </span>
                        </div>

                        <div class="space-y-2 min-h-[500px]" id="status-{{ $status->value }}">
                            @foreach($statusTickets as $ticket)
                                <div
                                    wire:key="ticket-{{ $ticket->id }}"
                                    draggable="true"
                                    data-ticket-id="{{ $ticket->id }}"
                                    data-status="{{ $ticket->status->value }}"
                                    wire:loading.class="saving-ticket"
                                    wire:target="deleteTicket({{ $ticket->id }})"
                                    wire:loading.style="border-color: rgb(59, 130, 246) !important; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;"
                                    x-data="{ 
                                        contextMenuOpen: false,
                                        priorityMenuOpen: false
                                    }"
                                    x-init="
                                        const ticketCard = $el;
                                        if (window.Livewire) {
                                            Livewire.on('ticket-priority-updated', (data) => {
                                                if (data.ticketId == {{ $ticket->id }}) {
                                                    ticketCard.classList.remove('saving-ticket');
                                                    ticketCard.style.borderColor = '';
                                                    ticketCard.style.boxShadow = '';
                                                    
                                                    // Update priority badge
                                                    if (data.priority !== undefined) {
                                                        const priorityBadge = document.querySelector(`[data-ticket-priority-badge='{{ $ticket->id }}']`);
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
                                        } else {
                                            document.addEventListener('livewire:init', () => {
                                                Livewire.on('ticket-priority-updated', (data) => {
                                                    if (data.ticketId == {{ $ticket->id }}) {
                                                        ticketCard.classList.remove('saving-ticket');
                                                        ticketCard.style.borderColor = '';
                                                        ticketCard.style.boxShadow = '';
                                                        
                                                        // Update priority badge
                                                        if (data.priority !== undefined) {
                                                            const priorityBadge = document.querySelector(`[data-ticket-priority-badge='{{ $ticket->id }}']`);
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
                                            });
                                        }
                                    "
                                    x-on:dragstart="handleDragStart($event, {{ $ticket->id }})"
                                    x-on:dragend="handleDragEnd($event)"
                                    x-on:close-all-menus.window="contextMenuOpen = false; priorityMenuOpen = false"
                                    x-on:click.away="contextMenuOpen = false; priorityMenuOpen = false"
                                    @click="
                                        if (!$event.target.closest('.menu-button') && !$event.target.closest('.context-menu') && !$event.target.closest('.priority-menu')) {
                                            contextMenuOpen = false;
                                            priorityMenuOpen = false;
                                        }
                                    "
                                    class="relative bg-white dark:bg-gray-900 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 cursor-move hover:shadow-md transition-shadow overflow-visible"
                                >
                                    <div class="absolute top-1.5 left-1 right-1 z-10 flex items-center justify-between gap-0.5">
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-300">
                                            {{ $ticket->formatted_code }}
                                        </span>

                                        <span class="text-xs text-gray-600 dark:text-gray-400 font-medium">
                                            {{ $ticket->department?->name }}
                                        </span>

                                        @if($ticket->priority)
                                            @php
                                                $priorityBadgeClass = match($ticket->priority->getColor()) {
                                                    'success' => 'bg-success-100 text-success-700 dark:bg-success-900 dark:text-success-300',
                                                    'danger' => 'bg-danger-100 text-danger-700 dark:bg-danger-900 dark:text-danger-300',
                                                    'warning' => 'bg-warning-100 text-warning-700 dark:bg-warning-900 dark:text-warning-300',
                                                    'info' => 'bg-info-100 text-info-700 dark:bg-info-900 dark:text-info-300',
                                                    'gray' => 'bg-gray-100 text-gray-700 dark:bg-gray-900 dark:text-gray-300',
                                                    default => 'bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-300',
                                                };
                                            @endphp
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium {{ $priorityBadgeClass }}" data-ticket-priority-badge="{{ $ticket->id }}">
                                                {{ $ticket->priority->getLabel() }}
                                            </span>
                                        @else
                                            <span></span>
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
                                                {{ $ticket->subject }}
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
                                            @if($ticket->client)
                                                <div class="flex items-center gap-1 text-gray-600 dark:text-gray-400">
                                                    <x-heroicon-o-building-office class="h-3 w-3 flex-shrink-0" />
                                                    <span class="truncate">{{ $ticket->client->name }}</span>
                                                </div>
                                            @endif

                                            @if($ticket->project)
                                                <div class="flex items-center gap-1 text-gray-600 dark:text-gray-400">
                                                    <x-heroicon-o-folder class="h-3 w-3 flex-shrink-0" />
                                                    <span class="truncate">{{ $ticket->project->name }}</span>
                                                </div>
                                            @endif

                                            @if($ticket->department)
                                                <div class="flex items-center gap-1 text-gray-600 dark:text-gray-400">
                                                    <x-heroicon-o-building-office-2 class="h-3 w-3 flex-shrink-0" />
                                                    <span class="truncate">{{ $ticket->department->name }}</span>
                                                </div>
                                            @endif

                                            @if($ticket->assignedTo)
                                                <div class="flex items-center gap-1 text-gray-600 dark:text-gray-400">
                                                    <x-heroicon-o-user class="h-3 w-3 flex-shrink-0" />
                                                    <span class="truncate">{{ $ticket->assignedTo->name }}</span>
                                                </div>
                                            @endif

                                            @if($ticket->updated_at)
                                                <div class="flex items-center gap-1 text-gray-500 dark:text-gray-500 pt-1 border-t border-gray-200 dark:border-gray-700">
                                                    <x-heroicon-o-clock class="h-3 w-3 flex-shrink-0" />
                                                    <span class="truncate">{{ __('Last update: :date', ['date' => $ticket->updated_at->translatedFormat('M d, Y H:i')]) }}</span>
                                                </div>
                                            @endif
                                        </div>
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
                                            href="{{ route('filament.admin.resources.tickets.edit', $ticket) }}"
                                            class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                        >
                                            <x-filament::icon icon="heroicon-o-pencil" class="h-4 w-4" />
                                            <span>{{ __('Edit') }}</span>
                                        </a>
                                        
                                        <div class="relative">
                                            <button 
                                                type="button"
                                                @click="priorityMenuOpen = !priorityMenuOpen"
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
                                                @foreach(\App\Core\Enums\TicketPriority::cases() as $priority)
                                                    <button 
                                                        type="button"
                                                        wire:click="updateTicketPriority({{ $ticket->id }}, '{{ $priority->value }}')"
                                                        @click="
                                                            contextMenuOpen = false; 
                                                            priorityMenuOpen = false;
                                                            const card = $el.closest('[data-ticket-id=\'{{ $ticket->id }}\']');
                                                            if (card) {
                                                                card.classList.add('saving-ticket');
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
                                                        @if($ticket->priority?->value === $priority->value)
                                                            <x-filament::icon icon="heroicon-o-check" class="h-4 w-4 ml-auto text-primary-500" />
                                                        @endif
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>
                                        
                                        <button 
                                            type="button"
                                            wire:click="deleteTicket({{ $ticket->id }})"
                                            wire:confirm="{{ __('Are you sure you want to delete this ticket?') }}"
                                            @click="contextMenuOpen = false"
                                            class="w-full flex items-center gap-2 px-4 py-2 text-sm text-danger-600 dark:text-danger-400 hover:bg-danger-50 dark:hover:bg-danger-900/20 transition-colors"
                                        >
                                            <x-filament::icon icon="heroicon-o-trash" class="h-4 w-4" />
                                            <span>{{ __('Delete') }}</span>
                                        </button>
                                    </div>

                                    @php
                                        $tasks = $ticket->tasks ?? collect();
                                        $totalTasks = $tasks->count();
                                        $completedTasks = $tasks->where('status', \App\Core\Enums\TaskStatus::COMPLETED)->count();
                                        $progress = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;
                                    @endphp

                                    @if($totalTasks > 0)
                                        <div class="absolute bottom-0 left-0 right-0 h-1 bg-gray-200 dark:bg-gray-700" data-ticket-progress-bar-container="{{ $ticket->id }}">
                                            <div class="h-full bg-primary-500 transition-all duration-300" data-ticket-progress-bar="{{ $ticket->id }}" style="width: {{ $progress }}%"></div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                            <div class="text-center py-6 text-gray-500 dark:text-gray-400 text-sm" data-empty-placeholder style="display: {{ $statusTickets->count() === 0 ? '' : 'none' }};">
                                {{ __('No tickets') }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        .saving-ticket {
            position: relative;
            overflow: hidden;
        }

        .saving-ticket::before {
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
            animation: shimmer-ticket 1.5s infinite;
            z-index: 1;
            pointer-events: none;
        }

        @keyframes shimmer-ticket {
            0% {
                left: -100%;
            }
            100% {
                left: 100%;
            }
        }

        .dark .saving-ticket::before {
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
        </script>
    @endPushOnce

<x-filament-actions::modals />
</x-filament-panels::page>

