<?php

namespace App\Providers;

use Modules\Master\Entities\Facility;
use Modules\Core\Observers\DatabaseNotificationObserver;
use Modules\Core\Observers\FacilityObserver;
use Modules\Core\Services\TenantDatabaseService;
use BezhanSalleh\LanguageSwitch\LanguageSwitch;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Events\MigrationEnded;
use Illuminate\Database\Events\MigrationStarted;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use ReflectionClass;
use ReflectionException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register model observers
        Facility::observe(FacilityObserver::class);
        DatabaseNotification::observe(DatabaseNotificationObserver::class);

        // Set URL defaults for subdomain routing
        if (! app()->runningInConsole()) {
            URL::defaults([
                'subdomain' => getCurrentSubdomain(),
            ]);
        }

        // Disable mass assignment protection for all models
        Model::unguard();

        // Automatically eager load relationships for all models
        Model::automaticallyEagerLoadRelationships();

        // Implicitly grant "Super Admin" role all permissions
        // This works in the app by using gate-related functions like auth()->user->can() and @can()
        Gate::before(function ($user, $ability) {
            return $user->hasRole('SuperAdmin') ? true : null;
        });

        // Fix menu scrolling issue
        FilamentAsset::register(
            assets: [
                Js::make('filament/menu-scroll-fix-v4', __DIR__.'/../../public/js/app/filament/menu-scroll-fix-v4.js'),
            ],
        );

        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch
                ->locales(['ar', 'en', 'ku']); // also accepts a closure
        });

        Table::configureUsing(function (Table $table): void {
            $table
                ->deferColumnManager(false)
                ->deferFilters(false)
                // ->persistFiltersInSession()
                ->persistSortInSession()
                // ->persistSearchInSession()
                // ->persistColumnSearchesInSession()
                ->striped()
                ->defaultSort('created_at', 'desc')
                ->reorderableColumns()
                ->filtersLayout(FiltersLayout::AboveContent)
                ->paginationPageOptions([10, 25, 50]);
        });

        // DB::listen(function ($query) {
        //     Log::info('SQL Query', [
        //         'sql' => $query->sql,
        //     ]);
        // });

        // DB::listen(function ($query) {
        //     Log::info('SQL Query', [
        //         'sql' => $query->sql,
        //     ]);
        // });

        Event::listen(MigrationStarted::class, function (MigrationStarted $event): void {
            $this->logTenantMigrationEvent('started', $event);
        });

        Event::listen(MigrationEnded::class, function (MigrationEnded $event): void {
            $this->logTenantMigrationEvent('completed', $event);
        });
    }

    private function logTenantMigrationEvent(string $status, MigrationStarted|MigrationEnded $event): void
    {
        if (! app()->runningInConsole()) {
            return;
        }

        $facility = TenantDatabaseService::getCurrentFacility();
        $onTenantConnection = config('database.default') === TenantDatabaseService::TENANT_CONNECTION;

        if (! $facility && ! $onTenantConnection) {
            return;
        }

        $logContext = [
            'migration_class' => get_class($event->migration),
            'migration_method' => $event->method,
            'connection' => config('database.default'),
        ];

        if ($facility) {
            $logContext['facility_id'] = $facility->id;
            $logContext['facility_subdomain'] = $facility->subdomain;
            $logContext['facility_name'] = $facility->name;
        }

        if ($fileName = $this->resolveMigrationFileName($event->migration)) {
            $logContext['migration_file'] = $fileName;
        }

        Log::info("Tenant migration {$status}", $logContext);
    }

    private function resolveMigrationFileName(object $migration): ?string
    {
        try {
            $reflection = new ReflectionClass($migration);

            return basename($reflection->getFileName() ?: '');
        } catch (ReflectionException) {
            return null;
        }
    }
}
