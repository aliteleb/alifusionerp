<x-filament-panels::page>
    <div class="space-y-6" x-data="{
        draggedOpportunityId: null,
        draggedStage: null,

        handleDragStart(event, opportunityId) {
            this.draggedOpportunityId = opportunityId;
            const opportunityElement = event.target.closest('[draggable=\'true\']');
            this.draggedStage = opportunityElement.dataset.stage;
            opportunityElement.style.opacity = '0.5';
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
                column.style.backgroundColor = 'rgba(59, 130, 246, 0.1)';
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

        handleDrop(event, newStage) {
            event.preventDefault();
            const column = event.currentTarget;
            this.removeDragOverStyle(column);

            // Find the dragged element
            const draggedElement = document.querySelector(`[data-opportunity-id='${this.draggedOpportunityId}']`);
            if (!draggedElement) {
                this.draggedOpportunityId = null;
                this.draggedStage = null;
                return;
            }

            if (this.draggedOpportunityId && this.draggedStage !== newStage) {
                // Store original column for rollback
                const originalStageContainer = draggedElement.closest('[id^=stage-]');
                const targetStageContainer = document.querySelector(`#stage-${newStage}`);
                
                if (!targetStageContainer) {
                    draggedElement.style.opacity = '1';
                    this.draggedOpportunityId = null;
                    this.draggedStage = null;
                    return;
                }

                // Optimistic update: Move element immediately in DOM
                draggedElement.style.opacity = '1';
                draggedElement.setAttribute('data-stage', newStage);
                targetStageContainer.appendChild(draggedElement);

                // Add loading/saving effect to the element
                this.addLoadingEffect(draggedElement);

                // Update counters immediately
                this.updateCounters();

                // Store original stage for rollback
                const originalStage = this.draggedStage;
                const opportunityId = this.draggedOpportunityId;

                // Reset drag state immediately
                this.draggedOpportunityId = null;
                this.draggedStage = null;

                // Update backend asynchronously (fire and forget, parallel execution)
                // Use setTimeout to allow parallel execution without blocking
                setTimeout(() => {
                    @this.call('updateOpportunityStage', opportunityId, newStage)
                        .then(() => {
                            // Remove loading effect
                            this.removeLoadingEffect(draggedElement);
                        })
                        .catch((error) => {
                            // Remove loading effect on error
                            this.removeLoadingEffect(draggedElement);
                            // Rollback on error: Move element back
                            if (originalStageContainer && draggedElement) {
                                originalStageContainer.appendChild(draggedElement);
                                draggedElement.setAttribute('data-stage', originalStage);
                                this.updateCounters();
                            }
                            console.error('Failed to update stage:', error);
                        });
                }, 0);
            } else {
                // Reset if same stage
                draggedElement.style.opacity = '1';
                this.draggedOpportunityId = null;
                this.draggedStage = null;
            }
        },

        updateCounters() {
            // Update all stage counters reactively
            document.querySelectorAll('[id^=stage-]').forEach(stageContainer => {
                const stageId = stageContainer.id.replace('stage-', '');
                const count = stageContainer.querySelectorAll('[draggable=\'true\']').length;
                // Find counter element by data attribute
                const counterElement = document.querySelector(`[data-counter-stage='${stageId}']`);
                if (counterElement) {
                    counterElement.textContent = count;
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
            element.style.backgroundColor = '';
        },

        addLoadingEffect(element) {
            // Add shimmer/loading class only
            element.classList.add('saving-opportunity');
            
            // Add a subtle border animation
            element.style.borderColor = 'rgb(59, 130, 246)';
            element.style.boxShadow = '0 0 0 3px rgba(59, 130, 246, 0.1)';
        },

        removeLoadingEffect(element) {
            // Remove loading class
            element.classList.remove('saving-opportunity');
            
            // Reset border and shadow
            element.style.borderColor = '';
            element.style.boxShadow = '';
        }
    }">
        {{-- Header with Actions and Filters --}}
        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
            <div class="flex items-center gap-4 flex-wrap">
                {{-- Branch Filter --}}
                <div class="w-full sm:w-auto">
                    <x-filament::input.wrapper>
                        <x-filament::input.select wire:model.live="branchId" placeholder="{{ __('All Branches') }}">
                            <option value="">{{ __('All Branches') }}</option>
                            @foreach(\App\Core\Models\Branch::active()->get()->sortBy('name') as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <x-filament::button
                    :href="route('filament.admin.resources.opportunities.index')"
                    tag="a"
                    color="gray"
                    outlined
                >
                    {{ __('List View') }}
                </x-filament::button>
                
                {{-- Header Actions --}}
                @foreach($this->getCachedHeaderActions() as $action)
                    {{ $action }}
                @endforeach
            </div>
        </div>

        {{-- Kanban Board --}}
        <div class="overflow-x-auto pb-6">
            <div class="flex gap-4 min-w-max">
                @foreach($this->getStages() as $stage)
                    @php
                        $stageOpportunities = $this->getOpportunitiesByStage($stage->value);
                    @endphp
                    <div
                        class="flex-shrink-0 w-80 bg-gray-50 dark:bg-gray-800 rounded-lg p-4"
                        x-on:drop="handleDrop($event, '{{ $stage->value }}')"
                        x-on:dragover.prevent="handleDragOver($event)"
                        x-on:dragenter.prevent="handleDragEnter($event)"
                        x-on:dragleave="handleDragLeave($event)"
                    >
                        {{-- Column Header --}}
                        <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center gap-2">
                                @php
                                    $stageIcon = $stage->getIcon();
                                @endphp
                                @if($stageIcon)
                                    @php
                                        $colorClass = match($stage->getColor()) {
                                            'success' => 'text-success-500',
                                            'danger' => 'text-danger-500',
                                            'warning' => 'text-warning-500',
                                            'info' => 'text-info-500',
                                            'gray' => 'text-gray-500',
                                            'secondary' => 'text-secondary-500',
                                            'purple' => 'text-purple-500',
                                            default => 'text-primary-500',
                                        };
                                    @endphp
                                    <x-filament::icon
                                        :icon="$stageIcon"
                                        class="h-5 w-5 {{ $colorClass }}"
                                    />
                                @endif
                                <h3 class="font-semibold text-gray-900 dark:text-gray-100">
                                    {{ $stage->getLabel() }}
                                </h3>
                            </div>
                            @php
                                $badgeBgClass = match($stage->getColor()) {
                                    'success' => 'bg-success-100 text-success-700 dark:bg-success-900 dark:text-success-300',
                                    'danger' => 'bg-danger-100 text-danger-700 dark:bg-danger-900 dark:text-danger-300',
                                    'warning' => 'bg-warning-100 text-warning-700 dark:bg-warning-900 dark:text-warning-300',
                                    'info' => 'bg-info-100 text-info-700 dark:bg-info-900 dark:text-info-300',
                                    'gray' => 'bg-gray-100 text-gray-700 dark:bg-gray-900 dark:text-gray-300',
                                    'secondary' => 'bg-secondary-100 text-secondary-700 dark:bg-secondary-900 dark:text-secondary-300',
                                    'purple' => 'bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-300',
                                    default => 'bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-300',
                                };
                            @endphp
                            <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badgeBgClass }}" data-counter-stage="{{ $stage->value }}">
                                {{ $stageOpportunities->count() }}
                            </span>
                        </div>

                        {{-- Opportunities List --}}
                        <div class="space-y-3 min-h-[500px]" id="stage-{{ $stage->value }}">
                            @forelse($stageOpportunities as $opportunity)
                                <div
                                    wire:key="opportunity-{{ $opportunity->id }}"
                                    draggable="true"
                                    data-opportunity-id="{{ $opportunity->id }}"
                                    data-stage="{{ $opportunity->stages->value }}"
                                    x-on:dragstart="handleDragStart($event, {{ $opportunity->id }})"
                                    x-on:dragend="handleDragEnd($event)"
                                    class="relative bg-white dark:bg-gray-900 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 cursor-move hover:shadow-md transition-shadow overflow-hidden"
                                    x-on:click="window.location.href='{{ route('filament.admin.resources.opportunities.edit', $opportunity) }}'"
                                    style="padding-bottom: {{ $opportunity->probability_of_winning > 0 ? '3px' : '0.75rem' }};"
                                >
                                    {{-- Badges - Fixed at top --}}
                                    <div class="absolute top-2 left-2 right-2 z-10 flex items-center justify-between gap-1">
                                        {{-- Revenue Badge --}}
                                        @if($opportunity->expected_revenue)
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-300">
                                                {{ number_format($opportunity->expected_revenue, 0) }}
                                            </span>
                                        @else
                                            <span></span>
                                        @endif

                                        {{-- Date Text --}}
                                        @if($opportunity->forecast_close_date)
                                            <span class="text-xs text-gray-600 dark:text-gray-400 font-medium">
                                                {{ $opportunity->forecast_close_date->format('M d, Y') }}
                                            </span>
                                        @else
                                            <span></span>
                                        @endif

                                        {{-- Priority Badge --}}
                                        @if($opportunity->priority)
                                            @php
                                                $priorityBadgeClass = match($opportunity->priority->getColor()) {
                                                    'success' => 'bg-success-100 text-success-700 dark:bg-success-900 dark:text-success-300',
                                                    'danger' => 'bg-danger-100 text-danger-700 dark:bg-danger-900 dark:text-danger-300',
                                                    'warning' => 'bg-warning-100 text-warning-700 dark:bg-warning-900 dark:text-warning-300',
                                                    'info' => 'bg-info-100 text-info-700 dark:bg-info-900 dark:text-info-300',
                                                    'gray' => 'bg-gray-100 text-gray-700 dark:bg-gray-900 dark:text-gray-300',
                                                    default => 'bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-300',
                                                };
                                            @endphp
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium {{ $priorityBadgeClass }}">
                                                {{ $opportunity->priority->getLabel() }}
                                            </span>
                                        @else
                                            <span></span>
                                        @endif
                                    </div>

                                    <div class="p-2.5 pt-10">
                                        {{-- Opportunity Name --}}
                                        <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-1.5 line-clamp-2 text-sm">
                                            {{ $opportunity->name }}
                                        </h4>

                                        {{-- Opportunity Details --}}
                                        <div class="space-y-1 text-xs">
                                            @if($opportunity->client)
                                                <div class="flex items-center gap-1.5 text-gray-600 dark:text-gray-400">
                                                    <x-heroicon-o-building-office class="h-3 w-3 flex-shrink-0" />
                                                    <span class="truncate">{{ $opportunity->client->name }}</span>
                                                </div>
                                            @endif

                                            @if($opportunity->assignedTo)
                                                <div class="flex items-center gap-1.5 text-gray-600 dark:text-gray-400">
                                                    <x-heroicon-o-user class="h-3 w-3 flex-shrink-0" />
                                                    <span class="truncate">{{ $opportunity->assignedTo->name }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Probability Bar as Border --}}
                                    @if($opportunity->probability_of_winning > 0)
                                        <div class="absolute bottom-0 left-0 right-0 h-1 bg-gray-200 dark:bg-gray-700">
                                            <div class="h-full bg-primary-500 transition-all duration-300" style="width: {{ $opportunity->probability_of_winning }}%"></div>
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="text-center py-8 text-gray-500 dark:text-gray-400 text-sm">
                                    {{ __('No opportunities') }}
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-filament-panels::page>

@push('styles')
<style>
    .saving-opportunity {
        position: relative;
        overflow: hidden;
    }
    
    .saving-opportunity::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(
            90deg,
            transparent 0%,
            rgba(59, 130, 246, 0.2) 50%,
            transparent 100%
        );
        animation: shimmer 1.5s infinite;
        z-index: 1;
        pointer-events: none;
    }
    
    @keyframes shimmer {
        0% {
            left: -100%;
        }
        100% {
            left: 100%;
        }
    }
    
    .dark .saving-opportunity::before {
        background: linear-gradient(
            90deg,
            transparent 0%,
            rgba(59, 130, 246, 0.25) 50%,
            transparent 100%
        );
    }
</style>
@endpush

