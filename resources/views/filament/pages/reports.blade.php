<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Header Section --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center space-x-3 mb-4">
                <div class="flex-shrink-0">
                    <x-heroicon-o-chart-bar class="h-8 w-8 text-primary-600 dark:text-primary-400" />
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ __('Reports Dashboard') }}
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400">
                        {{ __('Generate comprehensive reports for your business operations') }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Client Management Reports Section --}}
        <div class="mb-8">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">{{ __('Client Management Reports') }}</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                   {{-- Client Reports --}}
                   <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow">
                       <div class="flex items-center space-x-4 ">
                           <div class="flex-shrink-0">
                               <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                                   <x-heroicon-o-users class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                               </div>
                           </div>
                           <div class="flex-1 min-w-0">
                               <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                   {{ __('Client Reports') }}
                               </h3>
                               <p class="text-sm text-gray-600 dark:text-gray-400">
                                   {{ __('Client analytics, demographics, and engagement reports') }}
                               </p>
                           </div>
                       </div>
                       <div class="mt-4">
                           <x-filament::button
                               tag="a"
                               href="{{ route('filament.admin.pages.client-reports') }}"
                               color="primary"
                               size="sm"
                               class="bg-blue-600 hover:bg-blue-700">
                               {{ __('View Reports') }}
                               <x-heroicon-o-arrow-right class="ms-2 h-4 w-4 rtl:rotate-180" />
                           </x-filament::button>
                       </div>
                   </div>

                {{-- Contract Reports --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-center space-x-4 ">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-teal-100 dark:bg-teal-900 rounded-lg flex items-center justify-center">
                                <x-heroicon-o-document-text class="h-6 w-6 text-teal-600 dark:text-teal-400" />
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ __('Contract Reports') }}
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ __('Contract status, expiration tracking, and value analysis') }}
                            </p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <x-filament::button
                            tag="a"
                            href="{{ route('filament.admin.pages.contract-reports') }}"
                            color="primary"
                            size="sm"
                            class="bg-teal-600 hover:bg-teal-700">
                            {{ __('View Reports') }}
                            <x-heroicon-o-arrow-right class="ms-2 h-4 w-4 rtl:rotate-180" />
                        </x-filament::button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Project Management Reports Section --}}
        <div class="mb-8">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">{{ __('Project Management Reports') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {{-- Task Reports --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center space-x-4 ">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900 rounded-lg flex items-center justify-center">
                            <x-heroicon-o-clipboard-document-list class="h-6 w-6 text-orange-600 dark:text-orange-400" />
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ __('Task Reports') }}
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ __('Task completion, time tracking, and project progress reports') }}
                        </p>
                    </div>
                </div>
                <div class="mt-4">
                    <x-filament::button
                        tag="a"
                        href="{{ route('filament.admin.pages.task-reports') }}"
                        color="primary"
                        size="sm"
                        class="bg-orange-600 hover:bg-orange-700">
                        {{ __('View Reports') }}
                        @if(app()->getLocale() === 'ar' || app()->getLocale() === 'ku')
                            <x-heroicon-o-arrow-left class="me-2 h-4 w-4" />
                        @else
                            <x-heroicon-o-arrow-right class="ms-2 h-4 w-4" />
                        @endif
                    </x-filament::button>
                </div>
            </div>

            {{-- Project Reports --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center space-x-4">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900 rounded-lg flex items-center justify-center">
                            <x-heroicon-o-folder class="h-6 w-6 text-indigo-600 dark:text-indigo-400" />
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ __('Project Reports') }}
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ __('Project status, timeline, and resource utilization reports') }}
                        </p>
                    </div>
                </div>
                <div class="mt-4">
                    <x-filament::button
                        tag="a"
                        href="{{ route('filament.admin.pages.project-reports') }}"
                        color="primary"
                        size="sm"
                        class="bg-indigo-600 hover:bg-indigo-700">
                        {{ __('View Reports') }}
                        <x-heroicon-o-arrow-right class="ms-2 h-4 w-4" />
                    </x-filament::button>
                </div>
            </div>
            </div>
        </div>

        {{-- Sales Management Reports Section --}}
        <div class="mb-8">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">{{ __('Sales Management Reports') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                {{-- Opportunity Reports --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                                <x-heroicon-o-currency-dollar class="h-6 w-6 text-green-600 dark:text-green-400" />
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ __('Opportunity Reports') }}
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ __('Sales opportunities, pipeline analysis, and revenue forecasting') }}
                            </p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <x-filament::button
                            tag="a"
                            href="{{ route('filament.admin.pages.opportunity-reports') }}"
                            color="primary"
                            size="sm"
                            class="bg-green-600 hover:bg-green-700">
                            {{ __('View Reports') }}
                            <x-heroicon-o-arrow-right class="ms-2 h-4 w-4" />
                        </x-filament::button>
                    </div>
                </div>

                {{-- Deal Reports --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-emerald-100 dark:bg-emerald-900 rounded-lg flex items-center justify-center">
                                <x-heroicon-o-hand-raised class="h-6 w-6 text-emerald-600 dark:text-emerald-400" />
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ __('Deal Reports') }}
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ __('Deal pipeline, conversion rates, and sales performance metrics') }}
                            </p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <x-filament::button
                            tag="a"
                            href="{{ route('filament.admin.pages.deal-reports') }}"
                            color="primary"
                            size="sm"
                            class="bg-emerald-600 hover:bg-emerald-700">
                            {{ __('View Reports') }}
                            <x-heroicon-o-arrow-right class="ms-2 h-4 w-4" />
                        </x-filament::button>
                    </div>
                </div>

                {{-- Marketing Campaign Reports --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-cyan-100 dark:bg-cyan-900 rounded-lg flex items-center justify-center">
                                <x-heroicon-o-speaker-wave class="h-6 w-6 text-cyan-600 dark:text-cyan-400" />
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ __('Marketing Campaign Reports') }}
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ __('Campaign performance, ROI analysis, and marketing effectiveness') }}
                            </p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <x-filament::button
                            tag="a"
                            href="{{ route('filament.admin.pages.marketing-campaign-reports') }}"
                            color="primary"
                            size="sm"
                            class="bg-cyan-600 hover:bg-cyan-700">
                            {{ __('View Reports') }}
                            <x-heroicon-o-arrow-right class="ms-2 h-4 w-4" />
                        </x-filament::button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Support & Service Reports Section --}}
        <div class="mb-8">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">{{ __('Support & Service Reports') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                {{-- Ticket Reports --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-red-100 dark:bg-red-900 rounded-lg flex items-center justify-center">
                                <x-heroicon-o-ticket class="h-6 w-6 text-red-600 dark:text-red-400" />
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ __('Ticket Reports') }}
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ __('Support ticket analytics, resolution times, and customer satisfaction') }}
                            </p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <x-filament::button
                            tag="a"
                            href="{{ route('filament.admin.pages.ticket-reports') }}"
                            color="primary"
                            size="sm"
                            class="bg-red-600 hover:bg-red-700">
                            {{ __('View Reports') }}
                            <x-heroicon-o-arrow-right class="ms-2 h-4 w-4" />
                        </x-filament::button>
                    </div>
                </div>

                {{-- Complaint Reports --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900 rounded-lg flex items-center justify-center">
                                <x-heroicon-o-exclamation-triangle class="h-6 w-6 text-yellow-600 dark:text-yellow-400" />
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ __('Complaint Reports') }}
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ __('Complaint tracking, resolution analysis, and quality metrics') }}
                            </p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <x-filament::button
                            tag="a"
                            href="{{ route('filament.admin.pages.complaint-reports') }}"
                            color="primary"
                            size="sm"
                            class="bg-yellow-600 hover:bg-yellow-700">
                            {{ __('View Reports') }}
                            <x-heroicon-o-arrow-right class="ms-2 h-4 w-4" />
                        </x-filament::button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Organization Reports Section --}}
        <div class="mb-8">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">{{ __('Organization Reports') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                {{-- Branch Reports --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                                <x-heroicon-o-building-office-2 class="h-6 w-6 text-purple-600 dark:text-purple-400" />
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ __('Branch Reports') }}
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ __('Branch performance, user distribution, and operational metrics') }}
                            </p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <x-filament::button
                            tag="a"
                            href="{{ route('filament.admin.pages.branch-reports') }}"
                            color="primary"
                            size="sm"
                            class="bg-purple-600 hover:bg-purple-700">
                            {{ __('View Reports') }}
                            <x-heroicon-o-arrow-right class="ms-2 h-4 w-4" />
                        </x-filament::button>
                    </div>
            </div>

                {{-- Announcement Reports --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-pink-100 dark:bg-pink-900 rounded-lg flex items-center justify-center">
                                <x-heroicon-o-megaphone class="h-6 w-6 text-pink-600 dark:text-pink-400" />
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ __('Announcement Reports') }}
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ __('Announcement activity, engagement, and communication metrics') }}
                            </p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <x-filament::button
                            tag="a"
                            href="{{ route('filament.admin.pages.announcement-reports') }}"
                            color="primary"
                            size="sm"
                            class="bg-pink-600 hover:bg-pink-700">
                            {{ __('View Reports') }}
                            <x-heroicon-o-arrow-right class="ms-2 h-4 w-4" />
                        </x-filament::button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Actions Section --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                {{ __('Quick Actions') }}
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <x-filament::button
                    tag="a"
                    href="{{ route('reports.export.all', ['type' => 'pdf']) }}"
                    color="gray"
                    size="sm"
                    class="justify-center">
                    <x-heroicon-o-document-arrow-down class="h-5 w-5 me-2" />
                    {{ __('Export All Reports (PDF)') }}
                </x-filament::button>
                <x-filament::button
                    tag="a"
                    href="{{ route('reports.export.all', ['type' => 'excel']) }}"
                    color="gray"
                    size="sm"
                    class="justify-center">
                    <x-heroicon-o-table-cells class="h-5 w-5 me-2" />
                    {{ __('Export All Reports (Excel)') }}
                </x-filament::button>
                <x-filament::button
                    tag="a"
                    href="{{ route('reports.export.all', ['type' => 'csv']) }}"
                    color="gray"
                    size="sm"
                    class="justify-center">
                    <x-heroicon-o-document-text class="h-5 w-5 me-2" />
                    {{ __('Export All Reports (CSV)') }}
                </x-filament::button>
            </div>
        </div>
    </div>
</x-filament-panels::page>
