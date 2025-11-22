<?php

namespace Modules\Core\Filament\Pages;

use Modules\Core\Entities\Branch;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Filament\Panel;
use Filament\Support\Icons\Heroicon;
use Livewire\Attributes\Url;

class BranchReports extends Page
{
    protected static ?int $navigationSort = 4;

    public static function getNavigationParentItem(): ?string
    {
        return __('Reports');
    }

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::BuildingOffice2;

    public static function getNavigationGroup(): ?string
    {
        return __('Administration');
    }

    public static function getNavigationLabel(): string
    {
        return __('Branch Reports');
    }

    public static function shouldRegisterNavigation(?Panel $panel = null): bool
    {
        // Only show in admin panel, not in master panel
        $panel = $panel ?? Filament::getCurrentPanel();
        return $panel?->getId() === 'admin';
    }

    public function getTitle(): string
    {
        return __('Branch Reports');
    }

    public function getHeading(): string
    {
        return '';
    }

    protected string $view = 'filament.pages.branch-reports';

    protected static bool $hidePageTitle = true;

    // Filter properties with URL binding
    #[Url]
    public $status = 'all';

    #[Url]
    public $created_from = '';

    #[Url]
    public $created_to = '';

    #[Url]
    public $perPage = 10;

    public function mount(): void
    {
        // Initialize properties with default values or from URL parameters
        $this->status = request('status', 'all');
        $this->created_from = request('created_from', now()->subYears(1)->startOfMonth()->format('Y-m-d'));
        $this->created_to = request('created_to', now()->endOfMonth()->format('Y-m-d'));
        $this->perPage = request('per_page', 10);
    }

    public function applyFilters(): void
    {
        // With URL binding, Livewire automatically updates the URL when properties change
        // No need to manually handle URL updates
        // The properties are already bound to URL parameters via #[Url] attributes

        // Dispatch event to recreate charts with new data
        $this->dispatch('recreate-charts', chartData: $this->getChartData());
    }

    public function updatedPerPage(): void
    {
        // With URL binding, Livewire automatically updates the URL when perPage changes
        // No need to manually handle URL updates
        // The perPage property is already bound to URL parameters via #[Url] attribute
    }

    public function updatedStatus(): void
    {
        $this->dispatch('recreate-charts', chartData: $this->getChartData());
    }

    public function updatedCreatedFrom(): void
    {
        $this->dispatch('recreate-charts', chartData: $this->getChartData());
    }

    public function updatedCreatedTo(): void
    {
        $this->dispatch('recreate-charts', chartData: $this->getChartData());
    }

    public function getChartData(): array
    {
        return $this->chartData; // Returns the computed property
    }

    public function getReportData(): array
    {
        $query = Branch::query();

        // Get filter values from URL-bound properties
        $status = $this->status;
        $createdFrom = $this->created_from;
        $createdTo = $this->created_to;

        // Apply filters
        if (! empty($status) && $status !== 'all') {
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            } elseif ($status === 'hq') {
                $query->where('is_hq', true);
            }
        }

        if (! empty($createdFrom)) {
            $query->whereDate('created_at', '>=', $createdFrom);
        }

        if (! empty($createdTo)) {
            $query->whereDate('created_at', '<=', $createdTo);
        }

        // Get paginated results
        $branches = $query->paginate($this->perPage)->appends(request()->query());

        // Get monthly registration data (calculated from filtered query)
        $monthlyRegistrations = collect();

        // Determine the date range for monthly data
        $startDate = ! empty($createdFrom) ?
            \Carbon\Carbon::parse($createdFrom) :
            now()->subMonths(11)->startOfMonth();
        $endDate = ! empty($createdTo) ?
            \Carbon\Carbon::parse($createdTo) :
            now()->endOfMonth();

        // Generate monthly data within the selected range
        $current = $startDate->copy()->startOfMonth();
        while ($current->lte($endDate)) {
            $monthStart = $current->copy()->startOfMonth();
            $monthEnd = $current->copy()->endOfMonth();

            // Apply the same filters as the main query
            $monthQuery = Branch::query();

            if (! empty($status) && $status !== 'all') {
                if ($status === 'active') {
                    $monthQuery->where('is_active', true);
                } elseif ($status === 'inactive') {
                    $monthQuery->where('is_active', false);
                } elseif ($status === 'hq') {
                    $monthQuery->where('is_hq', true);
                }
            }

            $count = $monthQuery->whereBetween('created_at', [$monthStart, $monthEnd])->count();

            $monthlyRegistrations->push([
                'month' => $current->format('M Y'),
                'count' => $count,
            ]);

            $current->addMonth();
        }

        // Calculate stats from ALL filtered records, not just paginated ones
        $allFilteredQuery = Branch::query();

        // Apply the same filters to allFilteredQuery
        if (! empty($status) && $status !== 'all') {
            if ($status === 'active') {
                $allFilteredQuery->where('is_active', true);
            } elseif ($status === 'inactive') {
                $allFilteredQuery->where('is_active', false);
            } elseif ($status === 'hq') {
                $allFilteredQuery->where('is_hq', true);
            }
        }

        if (! empty($createdFrom)) {
            $allFilteredQuery->whereDate('created_at', '>=', $createdFrom);
        }

        if (! empty($createdTo)) {
            $allFilteredQuery->whereDate('created_at', '<=', $createdTo);
        }

        // Get statistics from ALL filtered records
        $allFilteredBranches = $allFilteredQuery->get();

        return [
            'branches' => $branches, // Paginated results (for table display)
            'total_branches' => $allFilteredBranches->count(), // ✅ All filtered records
            'active_branches' => $allFilteredBranches->where('is_active', true)->count(), // ✅ All filtered records
            'inactive_branches' => $allFilteredBranches->where('is_active', false)->count(), // ✅ All filtered records
            'hq_branches' => $allFilteredBranches->where('is_hq', true)->count(), // ✅ All filtered records
            'monthly_registrations' => $monthlyRegistrations,
        ];
    }

    public function getChartDataProperty(): array
    {
        // Get chart data based on ALL filtered records, not just paginated ones
        $query = Branch::query();

        // Apply the same filters as the main query
        if (! empty($this->status) && $this->status !== 'all') {
            if ($this->status === 'active') {
                $query->where('is_active', true);
            } elseif ($this->status === 'inactive') {
                $query->where('is_active', false);
            } elseif ($this->status === 'hq') {
                $query->where('is_hq', true);
            }
        }

        if (! empty($this->created_from)) {
            $query->whereDate('created_at', '>=', $this->created_from);
        }

        if (! empty($this->created_to)) {
            $query->whereDate('created_at', '<=', $this->created_to);
        }

        // Get status statistics from ALL filtered records
        $allFilteredBranches = $query->get();

        $statusStats = [
            'active' => $allFilteredBranches->where('is_active', true)->count(),
            'inactive' => $allFilteredBranches->where('is_active', false)->count(),
            'hq' => $allFilteredBranches->where('is_hq', true)->count(),
        ];

        // Get monthly registrations from ALL filtered records
        $monthlyRegistrations = collect();

        // Determine the date range for monthly data
        $startDate = ! empty($this->created_from) ?
            \Carbon\Carbon::parse($this->created_from) :
            now()->subMonths(11)->startOfMonth();
        $endDate = ! empty($this->created_to) ?
            \Carbon\Carbon::parse($this->created_to) :
            now()->endOfMonth();

        // Generate monthly data within the selected range
        $current = $startDate->copy()->startOfMonth();
        while ($current->lte($endDate)) {
            $monthStart = $current->copy()->startOfMonth();
            $monthEnd = $current->copy()->endOfMonth();

            // Apply the same filters as the main query
            $monthQuery = Branch::query();

            if (! empty($this->status) && $this->status !== 'all') {
                if ($this->status === 'active') {
                    $monthQuery->where('is_active', true);
                } elseif ($this->status === 'inactive') {
                    $monthQuery->where('is_active', false);
                } elseif ($this->status === 'hq') {
                    $monthQuery->where('is_hq', true);
                }
            }

            $count = $monthQuery->whereBetween('created_at', [$monthStart, $monthEnd])->count();

            $monthlyRegistrations->push([
                'month' => $current->format('M Y'),
                'count' => $count,
            ]);

            $current->addMonth();
        }

        return [
            'status' => $statusStats,
            'monthly' => $monthlyRegistrations,
        ];
    }
}
